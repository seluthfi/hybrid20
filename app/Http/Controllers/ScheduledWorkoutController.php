<?php

namespace App\Http\Controllers;

use App\Models\ScheduledWorkout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class ScheduledWorkoutController extends Controller
{
    /**
     * Update scheduled workout status, rescheduled_to, and instructions/notes.
     * PATCH /scheduled-workouts/{id}
     */
    public function update(Request $request, int $id): Response
    {
        $user = $request->user();
        $workout = ScheduledWorkout::whereHas('week.program', fn ($q) => $q->where('user_id', $user->id))
            ->findOrFail($id);

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'status' => ['required', Rule::in(['planned', 'completed', 'partial', 'skipped', 'rescheduled'])],
            'rescheduled_to' => [
                'nullable',
                Rule::requiredIf(fn () => $request->input('status') === 'rescheduled'),
                'date',
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        $updateData = [
            'status' => $validated['status'],
            'rescheduled_to' => $validated['status'] === 'rescheduled' ? $validated['rescheduled_to'] : null,
        ];

        if (isset($validated['notes'])) {
            $updateData['instructions'] = $validated['notes'];
        }

        $workout->update($updateData);

        if ($request->expectsJson()) {
            $statusLabels = [
                'planned' => 'Terjadwal',
                'completed' => 'Selesai',
                'partial' => 'Sebagian',
                'skipped' => 'Dilewati',
                'rescheduled' => 'Ditunda',
            ];

            return response()->json([
                'success' => true,
                'message' => 'Tersimpan',
                'scheduled_workout' => [
                    'id' => $workout->id,
                    'status' => $workout->status,
                    'status_label' => $statusLabels[$workout->status] ?? $workout->status,
                    'rescheduled_to' => $workout->rescheduled_to?->toDateString(),
                ],
            ]);
        }

        return back()->with('success', 'Status sesi berhasil diperbarui.');
    }

    /**
     * Skip scheduled workout (D23).
     * POST /scheduled-workouts/{id}/skip
     */
    public function skip(Request $request, int $id): Response
    {
        $user = $request->user();
        $workout = ScheduledWorkout::whereHas('week.program', fn ($q) => $q->where('user_id', $user->id))
            ->findOrFail($id);

        $workout->update([
            'status' => 'skipped',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tersimpan',
                'scheduled_workout' => [
                    'id' => $workout->id,
                    'status' => 'skipped',
                    'status_label' => 'Dilewati',
                ],
            ]);
        }

        return back()->with('success', 'Sesi ditandai sebagai dilewati.');
    }
}
