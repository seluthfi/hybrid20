<?php

namespace App\Http\Controllers;

use App\Models\DailyActivity;
use App\Support\Num;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DailyActivityController extends Controller
{
    /**
     * Upsert total steps for the specified date (R5, §8.6).
     * Replaces the day's total steps (not added to workouts.steps).
     */
    public function upsert(Request $request, string $date): Response
    {
        // Validate date
        try {
            $parsedDate = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => ['date' => ['Format tanggal tidak valid.']]], 422);
            }
            return back()->withErrors(['date' => 'Format tanggal tidak valid.']);
        }

        $rawSteps = $request->input('steps');
        $steps = Num::toInteger($rawSteps);

        if ($steps === null || $steps < 0) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => ['steps' => ['Jumlah langkah harus berupa angka positif.']]], 422);
            }
            return back()->withErrors(['steps' => 'Jumlah langkah harus berupa angka positif.']);
        }

        if ($steps > 200000) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => ['steps' => ['Jumlah langkah melebihi batas wajar.']]], 422);
            }
            return back()->withErrors(['steps' => 'Jumlah langkah melebihi batas wajar.']);
        }

        $user = Auth::user();

        $activity = DailyActivity::where('user_id', $user->id)
            ->whereDate('activity_date', $parsedDate)
            ->first();

        if ($activity) {
            $activity->update([
                'steps' => $steps,
                'source' => 'manual',
            ]);
        } else {
            DailyActivity::create([
                'user_id' => $user->id,
                'activity_date' => $parsedDate->toDateString(),
                'steps' => $steps,
                'source' => 'manual',
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tersimpan',
                'steps' => $steps,
                'formatted_steps' => Num::format($steps, 0),
                'activity_date' => $parsedDate->toDateString(),
            ]);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Langkah harian berhasil disimpan.',
        ]);
    }
}
