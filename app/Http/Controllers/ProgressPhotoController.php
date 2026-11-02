<?php

namespace App\Http\Controllers;

use App\Models\BodyMeasurement;
use App\Models\ProgressPhoto;
use App\Models\Program;
use App\Models\UserSetting;
use App\Services\ProgressPhotoService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProgressPhotoController extends Controller
{
    public function __construct(
        protected ProgressPhotoService $photoService
    ) {}

    /**
     * Display chronological private photos gallery (PLAN.md §10.9).
     * GET /progress-photos
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $settings = UserSetting::where('user_id', $user->id)->first();
        $timezone = $settings?->timezone ?? 'Asia/Jakarta';

        $program = Program::where('user_id', $user->id)->where('status', 'active')->first();
        if (! $program) {
            $program = Program::where('user_id', $user->id)->latest()->first();
        }

        $photos = ProgressPhoto::where('user_id', $user->id)
            ->orderByDesc('taken_at')
            ->orderByDesc('id')
            ->get();

        // Group photos by date
        $grouped = $photos->groupBy(function ($photo) {
            return $photo->taken_at->toDateString();
        });

        $groups = [];
        foreach ($grouped as $dateStr => $datePhotos) {
            $date = Carbon::parse($dateStr, $timezone);
            $weekLabel = 'Sebelum program';

            if ($program && $program->start_date) {
                $start = Carbon::parse($program->start_date)->startOfDay();
                if ($date->gte($start)) {
                    $diffWeeks = (int) floor($start->diffInDays($date) / 7) + 1;
                    $weekLabel = "Minggu {$diffWeeks}";
                }
            }

            // Measurement on this date
            $measurement = BodyMeasurement::where('user_id', $user->id)
                ->whereDate('measured_at', $dateStr)
                ->first();

            $groups[] = [
                'date' => $date,
                'date_string' => $dateStr,
                'date_formatted' => $date->translatedFormat('l, d M Y'),
                'week_label' => $weekLabel,
                'weight_kg' => $measurement?->weight_kg,
                'photos' => $datePhotos,
            ];
        }

        return view('body.photos', compact('groups', 'photos'));
    }

    /**
     * Store uploaded progress photo (PLAN.md §12).
     * POST /progress-photos
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'photo' => ['required', 'file', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
            'taken_at' => ['required', 'date_format:Y-m-d'],
            'pose_label' => ['required', Rule::in(['front', 'side', 'back', 'other'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->photoService->storePhoto(
            $user,
            $request->file('photo'),
            $validated['taken_at'],
            $validated['pose_label'],
            $validated['notes'] ?? null
        );

        return redirect()->route('progress-photos.index')->with('toast', [
            'type' => 'success',
            'message' => 'Foto progres berhasil disimpan dengan aman.',
        ]);
    }

    /**
     * Stream authorized private full-size image (Cache-Control: private, no-store).
     * GET /progress-photos/{id}/image
     */
    public function image(int $id): BinaryFileResponse
    {
        $user = Auth::user();
        $photo = ProgressPhoto::where('user_id', $user->id)->findOrFail($id);

        $path = Storage::disk('local')->path($photo->photo_path);

        abort_unless(file_exists($path), 404);

        $response = response()->file($path, [
            'Content-Type' => $photo->mime ?? 'image/jpeg',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-store');
        $response->headers->addCacheControlDirective('must-revalidate');

        return $response;
    }

    /**
     * Stream authorized private thumbnail (Cache-Control: private, no-store).
     * GET /progress-photos/{id}/thumb
     */
    public function thumb(int $id): BinaryFileResponse
    {
        $user = Auth::user();
        $photo = ProgressPhoto::where('user_id', $user->id)->findOrFail($id);

        $path = Storage::disk('local')->path($photo->thumb_path);

        abort_unless(file_exists($path), 404);

        $response = response()->file($path, [
            'Content-Type' => $photo->mime ?? 'image/jpeg',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-store');
        $response->headers->addCacheControlDirective('must-revalidate');

        return $response;
    }

    /**
     * Side-by-side comparison of 2 photos with identical pose (PLAN.md §10.9).
     * GET /body/photos/compare?a=&b=
     */
    public function compare(Request $request): View
    {
        $user = Auth::user();

        $photoAId = $request->query('a');
        $photoBId = $request->query('b');

        $photoA = $photoAId ? ProgressPhoto::where('user_id', $user->id)->find($photoAId) : null;
        $photoB = $photoBId ? ProgressPhoto::where('user_id', $user->id)->find($photoBId) : null;

        // If not selected, select 2 photos with the same pose (earliest and latest)
        if (! $photoA || ! $photoB) {
            $frontPhotos = ProgressPhoto::where('user_id', $user->id)
                ->where('pose_label', 'front')
                ->orderBy('taken_at')
                ->get();

            if ($frontPhotos->count() >= 2) {
                $photoA = $frontPhotos->first();
                $photoB = $frontPhotos->last();
            }
        }

        // Closest measurement (+- 3 days) for photo A
        $measurementA = null;
        if ($photoA) {
            $dateA = $photoA->taken_at->toDateString();
            $measurementA = BodyMeasurement::where('user_id', $user->id)
                ->whereNotNull('weight_kg')
                ->whereBetween('measured_at', [
                    $photoA->taken_at->copy()->subDays(3)->toDateString(),
                    $photoA->taken_at->copy()->addDays(3)->toDateString(),
                ])
                ->orderByRaw("ABS(julianday(measured_at) - julianday('{$dateA}')) ASC")
                ->first();
        }

        // Closest measurement (+- 3 days) for photo B
        $measurementB = null;
        if ($photoB) {
            $dateB = $photoB->taken_at->toDateString();
            $measurementB = BodyMeasurement::where('user_id', $user->id)
                ->whereNotNull('weight_kg')
                ->whereBetween('measured_at', [
                    $photoB->taken_at->copy()->subDays(3)->toDateString(),
                    $photoB->taken_at->copy()->addDays(3)->toDateString(),
                ])
                ->orderByRaw("ABS(julianday(measured_at) - julianday('{$dateB}')) ASC")
                ->first();
        }

        $deltaWeight = null;
        if ($measurementA && $measurementB && $measurementA->weight_kg !== null && $measurementB->weight_kg !== null) {
            $deltaWeight = (float) $measurementB->weight_kg - (float) $measurementA->weight_kg;
        }

        $allPhotos = ProgressPhoto::where('user_id', $user->id)->orderByDesc('taken_at')->get();

        return view('body.compare', compact(
            'photoA',
            'photoB',
            'measurementA',
            'measurementB',
            'deltaWeight',
            'allPhotos'
        ));
    }

    /**
     * Delete progress photo.
     * DELETE /progress-photos/{id}
     */
    public function destroy(ProgressPhoto $progressPhoto): RedirectResponse
    {
        abort_unless($progressPhoto->user_id === Auth::id(), 403);

        $this->photoService->deletePhoto($progressPhoto);

        return redirect()->route('progress-photos.index')->with('toast', [
            'type' => 'success',
            'message' => 'Foto progres berhasil dihapus.',
        ]);
    }
}
