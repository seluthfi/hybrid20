<?php

namespace App\Http\Controllers;

use App\Models\BodyMeasurement;
use App\Models\DailyActivity;
use App\Models\FoodLog;
use App\Models\Program;
use App\Models\ScheduledWorkout;
use App\Models\Workout;
use App\Services\ProgramDateService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TodayController extends Controller
{
    public function __construct(
        protected ProgramDateService $dateService
    ) {}

    public function index(): View
    {
        $user = Auth::user();
        $tz = $user->settings?->timezone ?? 'Asia/Jakarta';
        $todayLocal = Carbon::now($tz);
        $todayDate = $todayLocal->toDateString();

        $program = Program::where('user_id', $user->id)
            ->whereIn('status', ['planned', 'active', 'paused'])
            ->first();

        $programState = 'no_program';
        $currentPosition = null;
        $currentWeek = null;
        $scheduledWorkouts = collect();
        $weekSummary = collect();
        $daysUntilStart = 0;

        if ($program) {
            $totalWeeks = $program->weeks()->max('calendar_position') ?? ($program->duration_weeks ?? 20);
            $statusInfo = $this->dateService->programStatus($program->start_date, $totalWeeks, $todayLocal);

            if ($statusInfo['status'] === 'upcoming') {
                $programState = 'before_start';
                $daysUntilStart = $statusInfo['days_until_start'];
            } elseif ($statusInfo['status'] === 'completed') {
                $programState = 'completed';
            } else {
                $programState = 'active';
                $currentPosition = $statusInfo['active_position'];
                $currentWeek = $program->weeks()->where('calendar_position', $currentPosition)->first();

                if ($currentWeek) {
                    $weekday = $todayLocal->dayOfWeekIso;

                    $scheduledWorkouts = ScheduledWorkout::where(function ($q) use ($currentWeek, $weekday, $todayDate) {
                        $q->where('program_week_id', $currentWeek->id)
                          ->where('weekday', $weekday)
                          ->where(function ($sq) use ($todayDate) {
                              $sq->whereNull('rescheduled_to')
                                 ->orWhere('rescheduled_to', $todayDate);
                          });
                    })
                    ->orWhere(function ($q) use ($program, $todayDate) {
                        $q->whereHas('week', fn ($w) => $w->where('program_id', $program->id))
                          ->where('rescheduled_to', $todayDate);
                    })
                    ->with(['exercises', 'workouts' => function ($w) use ($user) {
                        $w->where('user_id', $user->id)->orderByDesc('id');
                    }])
                    ->orderBy('sort_order')
                    ->get();

                    $weekStart = $this->dateService->weekStartDate($program->start_date, $currentPosition);
                    for ($d = 1; $d <= 7; $d++) {
                        $dayDate = $weekStart->copy()->addDays($d - 1);
                        $dayWorkouts = ScheduledWorkout::where('program_week_id', $currentWeek->id)
                            ->where('weekday', $d)
                            ->get();

                        $status = 'planned';
                        if ($dayWorkouts->isEmpty()) {
                            $status = 'none';
                        } elseif ($dayWorkouts->contains('status', 'completed')) {
                            $status = 'completed';
                        } elseif ($dayWorkouts->contains('status', 'partial')) {
                            $status = 'partial';
                        } elseif ($dayWorkouts->contains('status', 'skipped')) {
                            $status = 'skipped';
                        }

                        $weekSummary->push([
                            'weekday' => $d,
                            'date' => $dayDate->toDateString(),
                            'day_name' => $dayDate->locale('id')->isoFormat('ddd'),
                            'day_num' => $dayDate->day,
                            'is_today' => $dayDate->isSameDay($todayLocal),
                            'status' => $status,
                        ]);
                    }
                }
            }
        }

        // Pain flag detection within last 14 days (§8.12)
        $fourteenDaysAgo = $todayLocal->copy()->subDays(14)->toDateString();
        $recentPainWorkouts = Workout::where('user_id', $user->id)
            ->where('pain_flag', true)
            ->whereDate('performed_on', '>=', $fourteenDaysAgo)
            ->orderByDesc('performed_on')
            ->get();

        $painNotice = null;
        if ($recentPainWorkouts->isNotEmpty()) {
            $count = $recentPainWorkouts->count();
            $prefix = $count >= 2 ? 'Ada catatan nyeri yang berulang. ' : '';
            $painNotice = [
                'count' => $count,
                'message' => $prefix . 'Pertimbangkan menghentikan progresi dan berkonsultasi dengan tenaga kesehatan.',
                'latest_workout_id' => $recentPainWorkouts->first()->id,
                'latest_workout_title' => $recentPainWorkouts->first()->title,
            ];
        }

        // Steps today
        $todayActivity = DailyActivity::where('user_id', $user->id)
            ->whereDate('activity_date', $todayDate)
            ->first();
        $todaySteps = $todayActivity?->steps ?? 0;

        // Food logs today (PLAN.md §8.7, §10.2)
        $todayCalories = (float) FoodLog::where('user_id', $user->id)
            ->whereDate('logged_on', $todayDate)
            ->sum('calories_kcal');
        $todayProtein = (float) FoodLog::where('user_id', $user->id)
            ->whereDate('logged_on', $todayDate)
            ->sum('protein_g');

        // Weight measurements & 7-day trend (D13, §8.8)
        $latestMeasurement = BodyMeasurement::where('user_id', $user->id)
            ->whereNotNull('weight_kg')
            ->orderByDesc('measured_at')
            ->first();

        $weightTrend = $this->calculateWeightTrend($user->id, $todayLocal);

        return view('today', compact(
            'program',
            'programState',
            'currentPosition',
            'currentWeek',
            'scheduledWorkouts',
            'weekSummary',
            'daysUntilStart',
            'todayLocal',
            'todayDate',
            'painNotice',
            'todaySteps',
            'todayCalories',
            'todayProtein',
            'latestMeasurement',
            'weightTrend'
        ));
    }

    /**
     * Calculate 7-day weight trend per D13 & §8.8.
     * "di hitung bila ada >= 4 pengukuran berat dalam 7 hari terakhir.
     * Selisih = rata-rata 7 hari terakhir dikurangi rata-rata 7 hari sebelumnya (butuh >= 4 data di masing-masing).
     * Selain itu tampil 'Belum cukup data (n dari 7 hari)'."
     */
    protected function calculateWeightTrend(int $userId, Carbon $todayLocal): array
    {
        $last7DaysStart = $todayLocal->copy()->subDays(6)->toDateString();
        $todayStr = $todayLocal->toDateString();

        $prev7DaysStart = $todayLocal->copy()->subDays(13)->toDateString();
        $prev7DaysEnd = $todayLocal->copy()->subDays(7)->toDateString();

        $recentMeasurements = BodyMeasurement::where('user_id', $userId)
            ->whereNotNull('weight_kg')
            ->whereDate('measured_at', '>=', $last7DaysStart)
            ->whereDate('measured_at', '<=', $todayStr)
            ->pluck('weight_kg');

        $countRecent = $recentMeasurements->count();

        if ($countRecent < 4) {
            return [
                'has_trend' => false,
                'label' => "Belum cukup data ({$countRecent} dari 7 hari)",
            ];
        }

        $prevMeasurements = BodyMeasurement::where('user_id', $userId)
            ->whereNotNull('weight_kg')
            ->whereDate('measured_at', '>=', $prev7DaysStart)
            ->whereDate('measured_at', '<=', $prev7DaysEnd)
            ->pluck('weight_kg');

        if ($prevMeasurements->count() < 4) {
            return [
                'has_trend' => false,
                'label' => "Belum cukup data ({$countRecent} dari 7 hari)",
            ];
        }

        $avgRecent = $recentMeasurements->avg();
        $avgPrev = $prevMeasurements->avg();
        $diff = round($avgRecent - $avgPrev, 1);

        if ($diff > 0) {
            $label = "Tren 7 hari: naik {$diff} kg";
        } elseif ($diff < 0) {
            $absDiff = abs($diff);
            $label = "Tren 7 hari: turun {$absDiff} kg";
        } else {
            $label = "Tren 7 hari: stabil 0,0 kg";
        }

        return [
            'has_trend' => true,
            'label' => $label,
            'diff' => $diff,
        ];
    }
}
