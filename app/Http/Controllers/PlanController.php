<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\ProgramWeek;
use App\Services\ProgramDateService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function __construct(
        protected ProgramDateService $dateService
    ) {}

    /**
     * Show program schedule for selected calendar position.
     * GET /plan/{calendar_position?}
     */
    public function show(Request $request, ?int $calendar_position = null): View|RedirectResponse
    {
        $user = $request->user();
        $program = Program::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (! $program) {
            // Check if there is any program
            $program = Program::where('user_id', $user->id)->latest()->first();
        }

        if (! $program) {
            return view('plan.index', [
                'hasProgram' => false,
                'program' => null,
                'allWeeks' => collect(),
                'currentWeek' => null,
                'scheduledDays' => [],
                'statusInfo' => null,
                'summary' => null,
            ]);
        }

        $allWeeks = ProgramWeek::where('program_id', $program->id)
            ->orderBy('calendar_position')
            ->get();

        $totalWeeks = $allWeeks->count();
        $statusInfo = $this->dateService->programStatus($program->start_date, $totalWeeks);

        // Determine which calendar position to view
        $targetPosition = $calendar_position ?? $statusInfo['active_position'];

        $currentWeek = $allWeeks->firstWhere('calendar_position', $targetPosition);
        if (! $currentWeek) {
            $currentWeek = $allWeeks->first();
            $targetPosition = $currentWeek ? $currentWeek->calendar_position : 1;
        }

        // Load workouts with their exercises
        $scheduledWorkouts = $currentWeek->scheduledWorkouts()
            ->with(['exercises.exercise'])
            ->orderBy('weekday')
            ->orderBy('sort_order')
            ->get();

        // Calculate summary for this week
        $runWorkouts = $scheduledWorkouts->where('workout_type', 'run');
        $gymWorkouts = $scheduledWorkouts->where('workout_type', 'gym');
        $totalSessions = $runWorkouts->count() + $gymWorkouts->count();
        $completedSessions = $scheduledWorkouts->whereIn('workout_type', ['run', 'gym'])->where('status', 'completed')->count();
        $partialSessions = $scheduledWorkouts->whereIn('workout_type', ['run', 'gym'])->where('status', 'partial')->count();
        $totalRunTargetKm = $runWorkouts->sum('planned_distance_km');

        // Total gym sets in week
        $totalGymSets = 0;
        foreach ($gymWorkouts as $gw) {
            $totalGymSets += $gw->exercises->sum('target_sets');
        }

        // Group scheduled workouts by weekday (1..7)
        // Also map date for each weekday
        $scheduledDays = [];
        $dayNames = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        for ($day = 1; $day <= 7; $day++) {
            $sessionDate = $this->dateService->sessionDate($program->start_date, $currentWeek->calendar_position, $day);
            $dayWorkouts = $scheduledWorkouts->where('weekday', $day)->values();

            $scheduledDays[$day] = [
                'weekday' => $day,
                'day_name' => $dayNames[$day],
                'date' => $sessionDate,
                'date_formatted' => $sessionDate->translatedFormat('d M Y'),
                'is_today' => $sessionDate->isToday(),
                'workouts' => $dayWorkouts,
            ];
        }

        return view('plan.index', [
            'hasProgram' => true,
            'program' => $program,
            'allWeeks' => $allWeeks,
            'currentWeek' => $currentWeek,
            'scheduledDays' => $scheduledDays,
            'statusInfo' => $statusInfo,
            'summary' => [
                'total_sessions' => $totalSessions,
                'completed_sessions' => $completedSessions,
                'partial_sessions' => $partialSessions,
                'total_run_target_km' => $totalRunTargetKm,
                'total_gym_sets' => $totalGymSets,
            ],
        ]);
    }

    /**
     * Update notes of a program week.
     * PATCH /program-weeks/{id}
     */
    public function updateNotes(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        $week = ProgramWeek::whereHas('program', fn ($q) => $q->where('user_id', $user->id))
            ->findOrFail($id);

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $week->update([
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Catatan minggu berhasil disimpan.');
    }

    /**
     * Repeat week: Duplicate the week and shift subsequent weeks by +1 (PLAN.md §8.10, D4).
     * POST /program-weeks/{id}/repeat
     */
    public function repeatWeek(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        $week = ProgramWeek::whereHas('program', fn ($q) => $q->where('user_id', $user->id))
            ->with(['scheduledWorkouts.exercises'])
            ->findOrFail($id);

        $program = $week->program;
        $currentPos = $week->calendar_position;

        DB::transaction(function () use ($program, $week, $currentPos) {
            // 1. Shift all subsequent weeks by +1
            // Important: Update in descending order of calendar_position to prevent unique constraint violation on (program_id, calendar_position)
            $subsequentWeeks = ProgramWeek::where('program_id', $program->id)
                ->where('calendar_position', '>', $currentPos)
                ->orderBy('calendar_position', 'desc')
                ->get();

            foreach ($subsequentWeeks as $subWeek) {
                $subWeek->update(['calendar_position' => $subWeek->calendar_position + 1]);
            }

            // 2. Create duplicated week at calendar_position = currentPos + 1
            $newWeek = ProgramWeek::create([
                'program_id' => $program->id,
                'week_number' => $week->week_number,
                'calendar_position' => $currentPos + 1,
                'is_repeat' => true,
                'phase_label' => $week->phase_label,
                'notes' => null,
            ]);

            // 3. Duplicate scheduled workouts and their exercises
            foreach ($week->scheduledWorkouts as $sw) {
                $newSw = $newWeek->scheduledWorkouts()->create([
                    'weekday' => $sw->weekday,
                    'workout_type' => $sw->workout_type,
                    'title' => $sw->title,
                    'planned_distance_km' => $sw->planned_distance_km,
                    'planned_duration_minutes' => $sw->planned_duration_minutes,
                    'intensity_label' => $sw->intensity_label,
                    'sort_order' => $sw->sort_order,
                    'instructions' => $sw->instructions,
                    'status' => 'planned',
                    'rescheduled_to' => null,
                ]);

                foreach ($sw->exercises as $swe) {
                    $newSw->exercises()->create([
                        'exercise_id' => $swe->exercise_id,
                        'exercise_name' => $swe->exercise_name,
                        'target_sets' => $swe->target_sets,
                        'target_reps' => $swe->target_reps,
                        'target_duration_seconds' => $swe->target_duration_seconds,
                        'sort_order' => $swe->sort_order,
                        'notes' => $swe->notes,
                    ]);
                }
            }
        });

        return redirect()->route('plan.show', ['calendar_position' => $currentPos + 1])
            ->with('success', 'Minggu berhasil diulang. Sisa program bergeser 1 minggu.');
    }

    /**
     * Cancel week repeat (PLAN.md §8.10, D4).
     * DELETE /program-weeks/{id}/repeat
     */
    public function cancelRepeat(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        $week = ProgramWeek::whereHas('program', fn ($q) => $q->where('user_id', $user->id))
            ->with(['scheduledWorkouts.workouts'])
            ->findOrFail($id);

        if (! $week->is_repeat) {
            return back()->withErrors(['repeat' => 'Minggu ini bukan minggu ulangan.']);
        }

        // Check if any scheduled workout has completed/partial status or has actual workouts attached
        foreach ($week->scheduledWorkouts as $sw) {
            if ($sw->status !== 'planned' || $sw->workouts->isNotEmpty()) {
                return back()->withErrors(['repeat' => 'Ulangan tidak bisa dibatalkan karena sudah ada sesi yang dicatat atau diubah.']);
            }
        }

        $program = $week->program;
        $deletedPos = $week->calendar_position;

        DB::transaction(function () use ($program, $week, $deletedPos) {
            // Delete the repeated week (cascade deletes scheduled_workouts and scheduled_workout_exercises)
            $week->delete();

            // Shift all subsequent weeks by -1
            // Ascending order is safe here because pos is moving into free slots
            $subsequentWeeks = ProgramWeek::where('program_id', $program->id)
                ->where('calendar_position', '>', $deletedPos)
                ->orderBy('calendar_position', 'asc')
                ->get();

            foreach ($subsequentWeeks as $subWeek) {
                $subWeek->update(['calendar_position' => $subWeek->calendar_position - 1]);
            }
        });

        $redirectPos = max(1, $deletedPos - 1);
        return redirect()->route('plan.show', ['calendar_position' => $redirectPos])
            ->with('success', 'Ulangan minggu berhasil dibatalkan.');
    }
}
