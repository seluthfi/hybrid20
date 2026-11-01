<?php

namespace App\Http\Controllers;

use App\Models\ScheduledWorkout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ScheduledWorkoutController extends Controller
{
    /**
     * Update scheduled workout status, rescheduled_to, and instructions/notes.
     * PATCH /scheduled-workouts/{id}
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        $workout = ScheduledWorkout::whereHas('week.program', fn ($q) => $q->where('user_id', $user->id))
            ->findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['planned', 'completed', 'partial', 'skipped', 'rescheduled'])],
            'rescheduled_to' => [
                'nullable',
                Rule::requiredIf(fn () => $request->input('status') === 'rescheduled'),
                'date',
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $updateData = [
            'status' => $validated['status'],
            'rescheduled_to' => $validated['status'] === 'rescheduled' ? $validated['rescheduled_to'] : null,
        ];

        // If notes is provided and instructions can store user session instructions or notes
        if (isset($validated['notes'])) {
            $updateData['instructions'] = $validated['notes'];
        }

        $workout->update($updateData);

        return back()->with('success', 'Status sesi berhasil diperbarui.');
    }

    /**
     * Skip scheduled workout (D23).
     * POST /scheduled-workouts/{id}/skip
     */
    public function skip(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        $workout = ScheduledWorkout::whereHas('week.program', fn ($q) => $q->where('user_id', $user->id))
            ->findOrFail($id);

        $workout->update([
            'status' => 'skipped',
        ]);

        return back()->with('success', 'Sesi ditandai sebagai dilewati.');
    }
}
