<?php

namespace App\Http\Controllers;

use App\Models\BodyMeasurement;
use App\Models\Exercise;
use App\Models\Program;
use App\Models\ProgramWeek;
use App\Models\ScheduledWorkout;
use App\Models\Workout;
use App\Models\WorkoutExercise;
use App\Services\ProgramDateService;
use App\Support\Num;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChartController extends Controller
{
    public function __construct(
        protected ProgramDateService $dateService
    ) {}

    /**
     * Body metric trend with rolling 7-day average (PLAN.md §8.8, §9.8, D13).
     * GET /charts/weight?metric=&range=
     */
    public function weight(Request $request): JsonResponse
    {
        $user = Auth::user();
        $tz = $user->settings?->timezone ?? 'Asia/Jakarta';

        $validMetrics = ['weight_kg', 'waist_cm', 'chest_cm', 'arm_cm', 'thigh_cm'];
        $metric = $request->query('metric', 'weight_kg');
        if (! in_array($metric, $validMetrics)) {
            $metric = 'weight_kg';
        }

        $validRanges = ['4w', '8w', 'all'];
        $range = $request->query('range', '4w');
        if (! in_array($range, $validRanges)) {
            $range = '4w';
        }

        $metricLabels = [
            'weight_kg' => 'Berat Badan',
            'waist_cm' => 'Lingkar Pinggang',
            'chest_cm' => 'Lingkar Dada',
            'arm_cm' => 'Lingkar Lengan',
            'thigh_cm' => 'Lingkar Paha',
        ];

        $metricUnits = [
            'weight_kg' => 'kg',
            'waist_cm' => 'cm',
            'chest_cm' => 'cm',
            'arm_cm' => 'cm',
            'thigh_cm' => 'cm',
        ];

        $query = BodyMeasurement::where('user_id', $user->id)
            ->whereNotNull($metric);

        $nowLocal = Carbon::now($tz);
        if ($range === '4w') {
            $startDate = $nowLocal->copy()->subWeeks(4)->toDateString();
            $query->whereDate('measured_at', '>=', $startDate);
        } elseif ($range === '8w') {
            $startDate = $nowLocal->copy()->subWeeks(8)->toDateString();
            $query->whereDate('measured_at', '>=', $startDate);
        }

        $records = $query->orderBy('measured_at')->get();

        if ($records->isEmpty()) {
            return response()->json([
                'empty' => true,
                'metric' => $metric,
                'metric_label' => $metricLabels[$metric],
                'unit' => $metricUnits[$metric],
                'range' => $range,
                'labels' => [],
                'dates' => [],
                'values' => [],
                'moving_averages' => [],
                'has_moving_average' => false,
            ]);
        }

        $labels = [];
        $dates = [];
        $values = [];
        $movingAverages = [];
        $hasMa = false;

        foreach ($records as $row) {
            $dateStr = $row->measured_at instanceof Carbon
                ? $row->measured_at->toDateString()
                : Carbon::parse($row->measured_at)->toDateString();

            $dateObj = Carbon::parse($dateStr, $tz);
            $val = (float) $row->$metric;

            $labels[] = $dateObj->translatedFormat('d M');
            $dates[] = $dateStr;
            $values[] = $val;

            // Rolling 7-day average: window [date - 6 days, date] requires >= 4 data points (PLAN.md §8.8)
            $windowStart = $dateObj->copy()->subDays(6)->toDateString();
            $windowMeasurements = BodyMeasurement::where('user_id', $user->id)
                ->whereNotNull($metric)
                ->whereDate('measured_at', '>=', $windowStart)
                ->whereDate('measured_at', '<=', $dateStr)
                ->pluck($metric);

            if ($windowMeasurements->count() >= 4) {
                $ma = round((float) $windowMeasurements->avg(), 2);
                $movingAverages[] = $ma;
                $hasMa = true;
            } else {
                $movingAverages[] = null;
            }
        }

        return response()->json([
            'empty' => false,
            'metric' => $metric,
            'metric_label' => $metricLabels[$metric],
            'unit' => $metricUnits[$metric],
            'range' => $range,
            'labels' => $labels,
            'dates' => $dates,
            'values' => $values,
            'moving_averages' => $movingAverages,
            'has_moving_average' => $hasMa,
        ]);
    }

    /**
     * 20-week running distance: planned target vs actual completed (PLAN.md §6.1, §9.8).
     * GET /charts/run-weekly
     */
    public function runWeekly(Request $request): JsonResponse
    {
        $user = Auth::user();
        $tz = $user->settings?->timezone ?? 'Asia/Jakarta';
        $todayLocal = Carbon::now($tz);

        $program = Program::where('user_id', $user->id)
            ->whereIn('status', ['planned', 'active', 'paused', 'completed'])
            ->first();

        if (! $program) {
            return response()->json([
                'empty' => true,
                'current_week' => null,
                'weeks' => [],
            ]);
        }

        $totalWeeks = $program->weeks()->max('calendar_position') ?? ($program->duration_weeks ?? 20);
        $statusInfo = $this->dateService->programStatus($program->start_date, $totalWeeks, $todayLocal);
        $activePosition = $statusInfo['status'] === 'active' ? $statusInfo['active_position'] : null;

        $weeksData = [];
        $programWeeks = ProgramWeek::where('program_id', $program->id)
            ->orderBy('calendar_position')
            ->get();

        if ($programWeeks->isEmpty()) {
            return response()->json([
                'empty' => true,
                'current_week' => null,
                'weeks' => [],
            ]);
        }

        $hasAnyActualRun = false;

        foreach ($programWeeks as $pw) {
            $pos = $pw->calendar_position;
            $weekStart = $this->dateService->weekStartDate($program->start_date, $pos);
            $weekEnd = $weekStart->copy()->addDays(6);

            // Planned target run km from template scheduled_workouts
            $plannedKm = (float) ScheduledWorkout::where('program_week_id', $pw->id)
                ->where('workout_type', 'run')
                ->sum('planned_distance_km');

            // Actual run km from logged workouts in this week's date range
            $actualKm = (float) Workout::where('user_id', $user->id)
                ->where('workout_type', 'run')
                ->whereDate('performed_on', '>=', $weekStart->toDateString())
                ->whereDate('performed_on', '<=', $weekEnd->toDateString())
                ->sum('distance_km');

            if ($actualKm > 0) {
                $hasAnyActualRun = true;
            }

            $weeksData[] = [
                'week_number' => $pw->week_number,
                'calendar_position' => $pos,
                'label' => "M{$pos}",
                'target_km' => round($plannedKm, 1),
                'actual_km' => round($actualKm, 1),
                'phase_label' => $pw->phase_label,
                'is_current' => ($pos === $activePosition),
            ];
        }

        return response()->json([
            'empty' => false,
            'has_runs' => $hasAnyActualRun,
            'current_position' => $activePosition,
            'weeks' => $weeksData,
        ]);
    }

    /**
     * Exercise progress history: max weight and volume per session (PLAN.md §9.8).
     * GET /charts/exercise-history?exercise_id=
     */
    public function exerciseHistory(Request $request): JsonResponse
    {
        $user = Auth::user();
        $tz = $user->settings?->timezone ?? 'Asia/Jakarta';

        // Get distinct exercises logged by this user
        $availableExercises = WorkoutExercise::whereHas('workout', fn ($w) => $w->where('user_id', $user->id))
            ->whereNotNull('exercise_id')
            ->select('exercise_id', 'exercise_name')
            ->distinct()
            ->get()
            ->map(fn ($we) => [
                'id' => $we->exercise_id,
                'name' => $we->exercise_name,
            ])
            ->values();

        if ($availableExercises->isEmpty()) {
            return response()->json([
                'empty' => true,
                'exercise' => null,
                'available_exercises' => [],
                'sessions' => [],
            ]);
        }

        $exerciseId = $request->query('exercise_id');
        $selectedExercise = null;

        if ($exerciseId) {
            $selectedExercise = $availableExercises->firstWhere('id', (int) $exerciseId);
        }

        if (! $selectedExercise) {
            $selectedExercise = $availableExercises->first();
        }

        // Fetch all workout exercise instances for this exercise
        $workoutExercises = WorkoutExercise::whereHas('workout', fn ($w) => $w->where('user_id', $user->id))
            ->where('exercise_id', $selectedExercise['id'])
            ->with(['workout', 'sets'])
            ->get();

        // Group by workout performed_on date
        $grouped = $workoutExercises->groupBy(function ($we) {
            return $we->workout?->performed_on?->toDateString()
                ?? Carbon::parse($we->workout?->performed_at)->toDateString();
        });

        $sessions = [];
        foreach ($grouped as $dateStr => $weList) {
            $dateObj = Carbon::parse($dateStr, $tz);
            $maxWeight = 0.0;
            $volume = 0.0;
            $setsCount = 0;

            foreach ($weList as $we) {
                foreach ($we->sets as $set) {
                    $weight = (float) $set->weight_kg;
                    $reps = (int) $set->reps;

                    if ($weight > $maxWeight) {
                        $maxWeight = $weight;
                    }

                    if (! $set->is_warmup && $weight > 0 && $reps > 0) {
                        $volume += ($weight * $reps);
                    }

                    $setsCount++;
                }
            }

            $sessions[] = [
                'date' => $dateStr,
                'formatted_date' => $dateObj->translatedFormat('d M'),
                'max_weight_kg' => round($maxWeight, 1),
                'volume_kg' => round($volume, 1),
                'sets_count' => $setsCount,
            ];
        }

        // Sort chronologically
        usort($sessions, fn ($a, $b) => strcmp($a['date'], $b['date']));

        return response()->json([
            'empty' => empty($sessions),
            'exercise' => $selectedExercise,
            'available_exercises' => $availableExercises,
            'sessions' => $sessions,
        ]);
    }
}
