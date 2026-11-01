<?php

namespace App\Http\Controllers;

use App\Models\DailyActivity;
use App\Support\Num;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DailyActivityController extends Controller
{
    /**
     * Upsert total steps for the specified date (R5, §8.6).
     * Replaces the day's total steps (not added to workouts.steps).
     */
    public function upsert(Request $request, string $date): RedirectResponse
    {
        // Validate date
        try {
            $parsedDate = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        } catch (\Exception $e) {
            return back()->withErrors(['date' => 'Format tanggal tidak valid.']);
        }

        $rawSteps = $request->input('steps');
        $steps = Num::toInteger($rawSteps);

        if ($steps === null || $steps < 0) {
            return back()->withErrors(['steps' => 'Jumlah langkah harus berupa angka positif.']);
        }

        if ($steps > 200000) {
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

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Langkah harian berhasil disimpan.',
        ]);
    }
}
