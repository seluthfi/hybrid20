<?php

namespace App\Http\Controllers;

use App\Models\DailyActivity;
use App\Models\Exercise;
use App\Models\ExerciseSet;
use App\Models\Program;
use App\Models\ScheduledWorkout;
use App\Models\Workout;
use App\Models\WorkoutExercise;
use App\Services\ProgramDateService;
use App\Support\Num;
use App\Support\PaceCalculator;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WorkoutController extends Controller
{
    public function __construct(
        protected ProgramDateService $dateService
    ) {}

    /**
     * Display a listing of workouts (Riwayat Latihan).
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $type = $request->query('type', 'all');
        $tz = $user->settings?->timezone ?? 'Asia/Jakarta';
        $todayLocal = Carbon::now($tz);

        $query = Workout::where('user_id', $user->id)
            ->with(['exercises.sets', 'scheduledWorkout'])
            ->orderByDesc('performed_on')
            ->orderByDesc('performed_at');

        if ($type === 'gym') {
            $query->where('workout_type', 'gym');
        } elseif ($type === 'run') {
            $query->where('workout_type', 'run');
        }

        $workouts = $query->paginate(15)->withQueryString();

        // Calculate weekly stats per §8.3 and §8.5
        $program = Program::where('user_id', $user->id)
            ->whereIn('status', ['planned', 'active', 'paused'])
            ->first();

        $weekLabel = 'Minggu Ini';
        $weeklyVolume = 0.0;
        $weeklyDistance = 0.0;
        $completedSessions = 0;
        $totalScheduled = 8;
        $partialCount = 0;

        if ($program) {
            $totalWeeks = $program->weeks()->max('calendar_position') ?? ($program->duration_weeks ?? 20);
            $statusInfo = $this->dateService->programStatus($program->start_date, $totalWeeks, $todayLocal);
            $currentPos = $statusInfo['active_position'];
            $week = $program->weeks()->where('calendar_position', $currentPos)->first();
            if ($week) {
                $weekLabel = "Minggu {$week->week_number}";
                $totalScheduled = $week->scheduledWorkouts()->whereIn('workout_type', ['gym', 'run'])->count() ?: 8;
                $completedSessions = $week->scheduledWorkouts()->where('status', 'completed')->count();
                $partialCount = $week->scheduledWorkouts()->where('status', 'partial')->count();

                $weekStart = $this->dateService->weekStartDate($program->start_date, $currentPos)->toDateString();
                $weekEnd = $this->dateService->weekStartDate($program->start_date, $currentPos)->addDays(6)->toDateString();

                $weekWorkouts = Workout::where('user_id', $user->id)
                    ->whereBetween('performed_on', [$weekStart, $weekEnd])
                    ->with('exercises.sets')
                    ->get();

                foreach ($weekWorkouts as $w) {
                    if ($w->workout_type === 'gym') {
                        $weeklyVolume += $w->calculateVolume();
                    } elseif ($w->workout_type === 'run') {
                        $weeklyDistance += (float) ($w->distance_km ?? 0);
                    }
                }
            }
        }

        return view('workouts.index', compact(
            'workouts', 
            'type', 
            'weekLabel', 
            'weeklyVolume', 
            'weeklyDistance', 
            'completedSessions', 
            'totalScheduled',
            'partialCount'
        ));
    }

    /**
     * Show the form for creating a new workout.
     */
    public function create(Request $request): View
    {
        $user = Auth::user();
        $scheduledId = $request->query('scheduled');
        $type = $request->query('type');

        $scheduledWorkout = null;
        if ($scheduledId) {
            $scheduledWorkout = ScheduledWorkout::with(['week.program', 'exercises.exercise'])
                ->whereHas('week.program', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->findOrFail($scheduledId);

            $type = $scheduledWorkout->workout_type;
        }

        // Timezone
        $tz = $user->settings?->timezone ?? 'Asia/Jakarta';
        $nowLocal = Carbon::now($tz);

        if ($type === 'run') {
            return view('workouts.create-run', compact('scheduledWorkout', 'nowLocal'));
        }

        // Default or gym
        $allExercises = Exercise::where(function ($q) use ($user) {
            $q->whereNull('user_id')->orWhere('user_id', $user->id);
        })->orderBy('name')->get();

        // Prepare previous set recommendations for gym exercises
        $previousSets = [];
        if ($scheduledWorkout && $scheduledWorkout->exercises->isNotEmpty()) {
            foreach ($scheduledWorkout->exercises as $schedEx) {
                $lastSet = ExerciseSet::whereHas('workoutExercise', function ($q) use ($user, $schedEx) {
                    $q->whereHas('workout', fn ($w) => $w->where('user_id', $user->id))
                      ->where('exercise_name', $schedEx->exercise_name);
                })
                ->where('is_warmup', false)
                ->orderByDesc('id')
                ->first();

                if ($lastSet) {
                    $previousSets[$schedEx->exercise_name] = [
                        'weight_kg' => (float) $lastSet->weight_kg,
                        'reps' => (int) $lastSet->reps,
                    ];
                }
            }
        }

        return view('workouts.create-gym', compact('scheduledWorkout', 'nowLocal', 'allExercises', 'previousSets'));
    }

    /**
     * Store a newly created workout.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $type = $request->input('workout_type', 'gym');
        $tz = $user->settings?->timezone ?? 'Asia/Jakarta';

        if ($type === 'run') {
            return $this->storeRun($request, $user, $tz);
        }

        return $this->storeGym($request, $user, $tz);
    }

    /**
     * Show the specified workout detail.
     */
    public function show(int $id): View
    {
        $user = Auth::user();
        $workout = Workout::where('user_id', $user->id)
            ->with(['exercises.sets', 'scheduledWorkout.week'])
            ->findOrFail($id);

        return view('workouts.show', compact('workout'));
    }

    /**
     * Show the form for editing the specified workout.
     */
    public function edit(int $id): View
    {
        $user = Auth::user();
        $workout = Workout::where('user_id', $user->id)
            ->with(['exercises.sets', 'scheduledWorkout.exercises'])
            ->findOrFail($id);

        $tz = $user->settings?->timezone ?? 'Asia/Jakarta';
        $performedAtLocal = Carbon::parse($workout->performed_at)->setTimezone($tz);

        if ($workout->workout_type === 'run') {
            return view('workouts.edit-run', compact('workout', 'performedAtLocal'));
        }

        $allExercises = Exercise::where(function ($q) use ($user) {
            $q->whereNull('user_id')->orWhere('user_id', $user->id);
        })->orderBy('name')->get();

        return view('workouts.edit-gym', compact('workout', 'performedAtLocal', 'allExercises'));
    }

    /**
     * Update the specified workout.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        $workout = Workout::where('user_id', $user->id)->findOrFail($id);
        $tz = $user->settings?->timezone ?? 'Asia/Jakarta';

        if ($workout->workout_type === 'run') {
            return $this->updateRun($request, $workout, $user, $tz);
        }

        return $this->updateGym($request, $workout, $user, $tz);
    }

    /**
     * Remove the specified workout.
     * Rule D23: Resetting scheduled workout status to planned if no other workouts attached.
     */
    public function destroy(int $id): RedirectResponse
    {
        $user = Auth::user();
        $workout = Workout::where('user_id', $user->id)->findOrFail($id);
        $scheduledWorkoutId = $workout->scheduled_workout_id;

        DB::transaction(function () use ($workout, $scheduledWorkoutId, $user) {
            $workout->delete();

            if ($scheduledWorkoutId) {
                $hasOtherWorkouts = Workout::where('user_id', $user->id)
                    ->where('scheduled_workout_id', $scheduledWorkoutId)
                    ->exists();

                if (!$hasOtherWorkouts) {
                    ScheduledWorkout::where('id', $scheduledWorkoutId)->update([
                        'status' => 'planned',
                    ]);
                }
            }
        });

        return redirect()->route('workouts.index')->with('toast', [
            'type' => 'success',
            'message' => 'Catatan sesi latihan berhasil dihapus.',
        ]);
    }

    /**
     * Store gym workout.
     */
    protected function storeGym(Request $request, $user, string $tz): RedirectResponse
    {
        $scheduledId = $request->input('scheduled_workout_id');
        $title = $request->input('title') ?: 'Latihan Gym';
        $durationSeconds = PaceCalculator::parseDurationToSeconds($request->input('duration'));
        $rawStartTime = $request->input('performed_at'); // local datetime-local

        try {
            $localDt = Carbon::parse($rawStartTime, $tz);
        } catch (\Exception $e) {
            $localDt = Carbon::now($tz);
        }

        $utcPerformedAt = $localDt->copy()->setTimezone('UTC');
        $performedOn = $localDt->toDateString();

        $rpe = Num::toInteger($request->input('rpe'));
        if ($rpe !== null && ($rpe < 1 || $rpe > 10)) {
            return back()->withInput()->withErrors(['rpe' => 'RPE harus di antara 1 dan 10.']);
        }

        $status = $request->input('status', 'completed');
        if (!in_array($status, ['completed', 'partial'])) {
            $status = 'completed';
        }

        $rawExercises = $request->input('exercises', []);

        DB::beginTransaction();
        try {
            $workout = Workout::create([
                'user_id' => $user->id,
                'scheduled_workout_id' => $scheduledId ?: null,
                'workout_type' => 'gym',
                'title' => $title,
                'performed_at' => $utcPerformedAt,
                'performed_on' => $performedOn,
                'duration_seconds' => $durationSeconds,
                'rpe' => $rpe,
                'status' => $status,
                'body_condition' => $request->input('body_condition'),
                'pain_flag' => (bool) $request->input('pain_flag', false),
                'pain_notes' => $request->input('pain_notes'),
                'general_notes' => $request->input('general_notes'),
            ]);

            // Save exercises and sets with Rule D24: only checked sets saved
            $exerciseSort = 1;
            foreach ($rawExercises as $exData) {
                $setsData = $exData['sets'] ?? [];
                $checkedSets = array_filter($setsData, fn ($s) => !empty($s['checked']));

                if (empty($checkedSets)) {
                    continue; // Skip exercise without any checked sets
                }

                $workoutExercise = WorkoutExercise::create([
                    'workout_id' => $workout->id,
                    'exercise_id' => $exData['exercise_id'] ?? null,
                    'exercise_name' => $exData['exercise_name'] ?? 'Latihan',
                    'sort_order' => $exerciseSort++,
                    'notes' => $exData['notes'] ?? null,
                ]);

                $setNumber = 1;
                foreach ($checkedSets as $setData) {
                    $setRpe = Num::toInteger($setData['rpe'] ?? null);
                    if ($setRpe !== null && ($setRpe < 1 || $setRpe > 10)) {
                        $setRpe = null;
                    }

                    ExerciseSet::create([
                        'workout_exercise_id' => $workoutExercise->id,
                        'set_number' => $setNumber++,
                        'reps' => Num::toInteger($setData['reps'] ?? 0) ?? 0,
                        'weight_kg' => Num::toDecimal($setData['weight_kg'] ?? 0) ?? 0.0,
                        'duration_seconds' => Num::toInteger($setData['duration_seconds'] ?? null),
                        'rpe' => $setRpe,
                        'is_warmup' => false,
                    ]);
                }
            }

            // Update scheduled workout status
            if ($scheduledId) {
                ScheduledWorkout::where('id', $scheduledId)->update([
                    'status' => $status,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['general' => 'Gagal menyimpan sesi: ' . $e->getMessage()]);
        }

        return redirect()->route('workouts.show', $workout->id)->with('toast', [
            'type' => 'success',
            'message' => 'Sesi gym berhasil disimpan.',
        ]);
    }

    /**
     * Update gym workout.
     */
    protected function updateGym(Request $request, Workout $workout, $user, string $tz): RedirectResponse
    {
        $durationSeconds = PaceCalculator::parseDurationToSeconds($request->input('duration'));
        $rawStartTime = $request->input('performed_at');

        try {
            $localDt = Carbon::parse($rawStartTime, $tz);
        } catch (\Exception $e) {
            $localDt = Carbon::parse($workout->performed_at)->setTimezone($tz);
        }

        $utcPerformedAt = $localDt->copy()->setTimezone('UTC');
        $performedOn = $localDt->toDateString();

        $rpe = Num::toInteger($request->input('rpe'));
        if ($rpe !== null && ($rpe < 1 || $rpe > 10)) {
            return back()->withInput()->withErrors(['rpe' => 'RPE harus di antara 1 dan 10.']);
        }

        $status = $request->input('status', $workout->status);
        if (!in_array($status, ['completed', 'partial'])) {
            $status = 'completed';
        }

        DB::beginTransaction();
        try {
            $workout->update([
                'title' => $request->input('title') ?: $workout->title,
                'performed_at' => $utcPerformedAt,
                'performed_on' => $performedOn,
                'duration_seconds' => $durationSeconds,
                'rpe' => $rpe,
                'status' => $status,
                'body_condition' => $request->input('body_condition'),
                'pain_flag' => (bool) $request->input('pain_flag', false),
                'pain_notes' => $request->input('pain_notes'),
                'general_notes' => $request->input('general_notes'),
            ]);

            // Recreate exercises and sets
            $workout->exercises()->delete();

            $rawExercises = $request->input('exercises', []);
            $exerciseSort = 1;
            foreach ($rawExercises as $exData) {
                $setsData = $exData['sets'] ?? [];
                $checkedSets = array_filter($setsData, fn ($s) => !empty($s['checked']));

                if (empty($checkedSets)) {
                    continue;
                }

                $workoutExercise = WorkoutExercise::create([
                    'workout_id' => $workout->id,
                    'exercise_id' => $exData['exercise_id'] ?? null,
                    'exercise_name' => $exData['exercise_name'] ?? 'Latihan',
                    'sort_order' => $exerciseSort++,
                    'notes' => $exData['notes'] ?? null,
                ]);

                $setNumber = 1;
                foreach ($checkedSets as $setData) {
                    $setRpe = Num::toInteger($setData['rpe'] ?? null);
                    if ($setRpe !== null && ($setRpe < 1 || $setRpe > 10)) {
                        $setRpe = null;
                    }

                    ExerciseSet::create([
                        'workout_exercise_id' => $workoutExercise->id,
                        'set_number' => $setNumber++,
                        'reps' => Num::toInteger($setData['reps'] ?? 0) ?? 0,
                        'weight_kg' => Num::toDecimal($setData['weight_kg'] ?? 0) ?? 0.0,
                        'duration_seconds' => Num::toInteger($setData['duration_seconds'] ?? null),
                        'rpe' => $setRpe,
                        'is_warmup' => false,
                    ]);
                }
            }

            if ($workout->scheduled_workout_id) {
                ScheduledWorkout::where('id', $workout->scheduled_workout_id)->update([
                    'status' => $status,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['general' => 'Gagal memperbarui sesi: ' . $e->getMessage()]);
        }

        return redirect()->route('workouts.show', $workout->id)->with('toast', [
            'type' => 'success',
            'message' => 'Sesi gym berhasil diperbarui.',
        ]);
    }

    /**
     * Store run workout.
     */
    protected function storeRun(Request $request, $user, string $tz): RedirectResponse
    {
        $scheduledId = $request->input('scheduled_workout_id');
        $title = $request->input('title') ?: 'Lari';
        $durationSeconds = PaceCalculator::parseDurationToSeconds($request->input('duration'));
        $distanceKm = Num::toDecimal($request->input('distance_km'));

        if ($distanceKm !== null && $distanceKm > 100.0) {
            return back()->withInput()->withErrors(['distance_km' => 'Jarak lari melebihi batas wajar (maks 100 km).']);
        }

        $paceSeconds = PaceCalculator::calculatePaceSeconds($durationSeconds, $distanceKm);

        $rawStartTime = $request->input('performed_at');
        try {
            $localDt = Carbon::parse($rawStartTime, $tz);
        } catch (\Exception $e) {
            $localDt = Carbon::now($tz);
        }

        $utcPerformedAt = $localDt->copy()->setTimezone('UTC');
        $performedOn = $localDt->toDateString();

        $rpe = Num::toInteger($request->input('rpe'));
        if ($rpe !== null && ($rpe < 1 || $rpe > 10)) {
            return back()->withInput()->withErrors(['rpe' => 'RPE harus di antara 1 dan 10.']);
        }

        $status = $request->input('status', 'completed');
        if (!in_array($status, ['completed', 'partial'])) {
            $status = 'completed';
        }

        $steps = Num::toInteger($request->input('steps'));

        DB::beginTransaction();
        try {
            $workout = Workout::create([
                'user_id' => $user->id,
                'scheduled_workout_id' => $scheduledId ?: null,
                'workout_type' => 'run',
                'title' => $title,
                'performed_at' => $utcPerformedAt,
                'performed_on' => $performedOn,
                'duration_seconds' => $durationSeconds,
                'distance_km' => $distanceKm,
                'pace_seconds_per_km' => $paceSeconds,
                'steps' => $steps,
                'rpe' => $rpe,
                'status' => $status,
                'body_condition' => $request->input('body_condition'),
                'pain_flag' => (bool) $request->input('pain_flag', false),
                'pain_notes' => $request->input('pain_notes'),
                'general_notes' => $request->input('general_notes'),
            ]);

            if ($scheduledId) {
                ScheduledWorkout::where('id', $scheduledId)->update([
                    'status' => $status,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['general' => 'Gagal menyimpan sesi lari: ' . $e->getMessage()]);
        }

        return redirect()->route('workouts.show', $workout->id)->with('toast', [
            'type' => 'success',
            'message' => 'Sesi lari berhasil disimpan.',
        ]);
    }

    /**
     * Update run workout.
     */
    protected function updateRun(Request $request, Workout $workout, $user, string $tz): RedirectResponse
    {
        $durationSeconds = PaceCalculator::parseDurationToSeconds($request->input('duration'));
        $distanceKm = Num::toDecimal($request->input('distance_km'));

        if ($distanceKm !== null && $distanceKm > 100.0) {
            return back()->withInput()->withErrors(['distance_km' => 'Jarak lari melebihi batas wajar (maks 100 km).']);
        }

        $paceSeconds = PaceCalculator::calculatePaceSeconds($durationSeconds, $distanceKm);

        $rawStartTime = $request->input('performed_at');
        try {
            $localDt = Carbon::parse($rawStartTime, $tz);
        } catch (\Exception $e) {
            $localDt = Carbon::parse($workout->performed_at)->setTimezone($tz);
        }

        $utcPerformedAt = $localDt->copy()->setTimezone('UTC');
        $performedOn = $localDt->toDateString();

        $rpe = Num::toInteger($request->input('rpe'));
        if ($rpe !== null && ($rpe < 1 || $rpe > 10)) {
            return back()->withInput()->withErrors(['rpe' => 'RPE harus di antara 1 dan 10.']);
        }

        $status = $request->input('status', $workout->status);
        if (!in_array($status, ['completed', 'partial'])) {
            $status = 'completed';
        }

        $steps = Num::toInteger($request->input('steps'));

        DB::beginTransaction();
        try {
            $workout->update([
                'title' => $request->input('title') ?: $workout->title,
                'performed_at' => $utcPerformedAt,
                'performed_on' => $performedOn,
                'duration_seconds' => $durationSeconds,
                'distance_km' => $distanceKm,
                'pace_seconds_per_km' => $paceSeconds,
                'steps' => $steps,
                'rpe' => $rpe,
                'status' => $status,
                'body_condition' => $request->input('body_condition'),
                'pain_flag' => (bool) $request->input('pain_flag', false),
                'pain_notes' => $request->input('pain_notes'),
                'general_notes' => $request->input('general_notes'),
            ]);

            if ($workout->scheduled_workout_id) {
                ScheduledWorkout::where('id', $workout->scheduled_workout_id)->update([
                    'status' => $status,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['general' => 'Gagal memperbarui sesi lari: ' . $e->getMessage()]);
        }

        return redirect()->route('workouts.show', $workout->id)->with('toast', [
            'type' => 'success',
            'message' => 'Sesi lari berhasil diperbarui.',
        ]);
    }
}
