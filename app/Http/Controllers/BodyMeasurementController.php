<?php

namespace App\Http\Controllers;

use App\Models\BodyMeasurement;
use App\Models\UserSetting;
use App\Support\Num;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BodyMeasurementController extends Controller
{
    /**
     * Display body measurements index, 7-day trend, and history list (PLAN.md §8.8, §10.9).
     * GET /body
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $settings = UserSetting::where('user_id', $user->id)->first();
        $timezone = $settings?->timezone ?? 'Asia/Jakarta';

        // Historical measurements ordered latest first
        $measurements = BodyMeasurement::where('user_id', $user->id)
            ->orderByDesc('measured_at')
            ->paginate(15);

        // Calculate latest measurement and 7-day trend (PLAN.md §8.8)
        $latest = BodyMeasurement::where('user_id', $user->id)
            ->whereNotNull('weight_kg')
            ->orderByDesc('measured_at')
            ->first();

        $daysAgoText = null;
        $trendText = null;
        $trendDiff = null;

        if ($latest) {
            $latestDate = Carbon::parse($latest->measured_at, $timezone);
            $today = Carbon::now($timezone)->startOfDay();
            $diffDays = (int) $latestDate->diffInDays($today);

            if ($diffDays === 0) {
                $daysAgoText = 'Hari ini';
            } elseif ($diffDays === 1) {
                $daysAgoText = '1 hari lalu';
            } else {
                $daysAgoText = "{$diffDays} hari lalu";
            }

            // 7-day window check: get measurements within last 7 days from latest measurement
            $sevenDaysAgo = $latestDate->copy()->subDays(6);
            $windowMeasurements = BodyMeasurement::where('user_id', $user->id)
                ->whereNotNull('weight_kg')
                ->whereDate('measured_at', '>=', $sevenDaysAgo->toDateString())
                ->whereDate('measured_at', '<=', $latestDate->toDateString())
                ->orderBy('measured_at')
                ->get();

            $dataCount = $windowMeasurements->count();

            if ($dataCount >= 4) {
                $oldestInWindow = $windowMeasurements->first();
                $diff = (float) $latest->weight_kg - (float) $oldestInWindow->weight_kg;
                $diffAbs = abs($diff);
                $diffFormatted = number_format($diffAbs, 1, ',', '.');

                if (abs($diff) < 0.1) {
                    $trendText = "Tren 7 hari: stabil {$diffFormatted} kg";
                } elseif ($diff < 0) {
                    $trendText = "Tren 7 hari: turun {$diffFormatted} kg";
                } else {
                    $trendText = "Tren 7 hari: naik {$diffFormatted} kg";
                }
                $trendDiff = $diff;
            } else {
                $trendText = "Belum cukup data ({$dataCount} dari 7 hari)";
            }
        }

        return view('body.index', compact(
            'measurements',
            'latest',
            'daysAgoText',
            'trendText',
            'trendDiff',
            'settings'
        ));
    }

    /**
     * Upsert body measurement for the given date (D14).
     * PUT /body/{date}
     */
    public function upsert(Request $request, string $date): \Symfony\Component\HttpFoundation\Response
    {
        try {
            $parsedDate = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => ['date' => ['Format tanggal tidak valid.']]], 422);
            }
            return back()->withErrors(['date' => 'Format tanggal tidak valid.']);
        }

        $user = Auth::user();

        $data = [];
        if ($request->has('weight_kg')) {
            $rawWeight = $request->input('weight_kg');
            $weight = Num::toDecimal($rawWeight);
            if ($rawWeight !== null && $rawWeight !== '' && ($weight === null || $weight < 20 || $weight > 300)) {
                if ($request->expectsJson()) {
                    return response()->json(['errors' => ['weight_kg' => ['Berat badan harus di antara 20 dan 300 kg.']]], 422);
                }
                return back()->withErrors(['weight_kg' => 'Berat badan harus di antara 20 dan 300 kg.']);
            }
            if ($weight !== null) {
                $data['weight_kg'] = $weight;
            }
        }

        if ($request->has('waist_cm')) {
            $raw = $request->input('waist_cm');
            if ($raw !== null && $raw !== '') {
                $data['waist_cm'] = Num::toDecimal($raw);
            }
        }
        if ($request->has('chest_cm')) {
            $raw = $request->input('chest_cm');
            if ($raw !== null && $raw !== '') {
                $data['chest_cm'] = Num::toDecimal($raw);
            }
        }
        if ($request->has('arm_cm')) {
            $raw = $request->input('arm_cm');
            if ($raw !== null && $raw !== '') {
                $data['arm_cm'] = Num::toDecimal($raw);
            }
        }
        if ($request->has('thigh_cm')) {
            $raw = $request->input('thigh_cm');
            if ($raw !== null && $raw !== '') {
                $data['thigh_cm'] = Num::toDecimal($raw);
            }
        }
        if ($request->has('notes')) {
            $data['notes'] = $request->input('notes');
        }

        if (empty($data)) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => ['weight_kg' => ['Isi minimal satu metrik pengukuran.']]], 422);
            }
            return back()->withErrors(['weight_kg' => 'Isi minimal satu metrik pengukuran.']);
        }

        $measurement = BodyMeasurement::where('user_id', $user->id)
            ->whereDate('measured_at', $parsedDate)
            ->first();

        if ($measurement) {
            $measurement->update($data);
        } else {
            $data['user_id'] = $user->id;
            $data['measured_at'] = $parsedDate->toDateString();
            BodyMeasurement::create($data);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tersimpan',
                'measured_at' => $parsedDate->toDateString(),
                'weight_kg' => $data['weight_kg'] ?? null,
                'formatted_weight' => isset($data['weight_kg']) ? Num::format($data['weight_kg'], 1) : null,
                'data' => $data,
            ]);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Pengukuran tubuh berhasil disimpan.',
        ]);
    }

    /**
     * Delete body measurement record.
     * DELETE /body/{date}
     */
    public function destroy(string $date): RedirectResponse
    {
        $user = Auth::user();
        $measurement = BodyMeasurement::where('user_id', $user->id)
            ->whereDate('measured_at', $date)
            ->firstOrFail();

        $measurement->delete();

        return redirect()->route('body.index')->with('toast', [
            'type' => 'success',
            'message' => 'Data pengukuran berhasil dihapus.',
        ]);
    }
}
