<?php

namespace App\Services;

use App\Models\BodyMeasurement;
use App\Models\DailyActivity;
use App\Models\Exercise;
use App\Models\ExerciseSet;
use App\Models\Food;
use App\Models\FoodLog;
use App\Models\Program;
use App\Models\ProgramWeek;
use App\Models\ProgressPhoto;
use App\Models\ScheduledWorkout;
use App\Models\ScheduledWorkoutExercise;
use App\Models\User;
use App\Models\UserSetting;
use App\Models\Workout;
use App\Models\WorkoutExercise;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use ZipArchive;

class BackupService
{
    protected string $disk = 'local'; // storage/app/private

    /**
     * Export all user data, database records, and photos into a structured ZIP archive.
     * (PLAN.md §8.13)
     */
    public function export(User $user): string
    {
        $tempDir = storage_path('app/private/temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipPath = $tempDir . '/backup_' . $user->id . '_' . date('Ymd_His') . '_' . Str::random(8) . '.zip';

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Gagal membuat berkas arsip ZIP pencadangan.');
        }

        // 1. Gather all user records
        $settings = UserSetting::where('user_id', $user->id)->get()->toArray();
        $customExercises = Exercise::where('user_id', $user->id)->get()->toArray();

        $programs = Program::where('user_id', $user->id)->get();
        $programIds = $programs->pluck('id')->all();

        $programWeeks = ProgramWeek::whereIn('program_id', $programIds)->get();
        $programWeekIds = $programWeeks->pluck('id')->all();

        $scheduledWorkouts = ScheduledWorkout::whereIn('program_week_id', $programWeekIds)->get();
        $scheduledWorkoutIds = $scheduledWorkouts->pluck('id')->all();

        $scheduledWorkoutExercises = ScheduledWorkoutExercise::whereIn('scheduled_workout_id', $scheduledWorkoutIds)->get()->toArray();

        $workouts = Workout::where('user_id', $user->id)->get();
        $workoutIds = $workouts->pluck('id')->all();

        $workoutExercises = WorkoutExercise::whereIn('workout_id', $workoutIds)->get();
        $workoutExerciseIds = $workoutExercises->pluck('id')->all();

        $exerciseSets = ExerciseSet::whereIn('workout_exercise_id', $workoutExerciseIds)->get()->toArray();

        $dailyActivities = DailyActivity::where('user_id', $user->id)->get()->toArray();
        $bodyMeasurements = BodyMeasurement::where('user_id', $user->id)->get()->toArray();

        $foods = Food::where('user_id', $user->id)->get()->toArray();
        $foodLogs = FoodLog::where('user_id', $user->id)->get()->toArray();

        $progressPhotos = ProgressPhoto::where('user_id', $user->id)->get();

        $data = [
            'user_settings' => $settings,
            'exercises' => $customExercises,
            'programs' => $programs->toArray(),
            'program_weeks' => $programWeeks->toArray(),
            'scheduled_workouts' => $scheduledWorkouts->toArray(),
            'scheduled_workout_exercises' => $scheduledWorkoutExercises,
            'workouts' => $workouts->toArray(),
            'workout_exercises' => $workoutExercises->toArray(),
            'exercise_sets' => $exerciseSets,
            'daily_activities' => $dailyActivities,
            'body_measurements' => $bodyMeasurements,
            'foods' => $foods,
            'food_logs' => $foodLogs,
            'progress_photos' => $progressPhotos->toArray(),
        ];

        $dataJson = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $zip->addFromString('data.json', $dataJson);

        $sha256 = [
            'data.json' => hash('sha256', $dataJson),
        ];

        // 2. Add photos to ZIP
        foreach ($progressPhotos as $photo) {
            if ($photo->photo_path && Storage::disk($this->disk)->exists($photo->photo_path)) {
                $mainBinary = Storage::disk($this->disk)->get($photo->photo_path);
                $mainZipPath = "photos/{$photo->id}.jpg";
                $zip->addFromString($mainZipPath, $mainBinary);
                $sha256[$mainZipPath] = hash('sha256', $mainBinary);
            }

            if ($photo->thumb_path && Storage::disk($this->disk)->exists($photo->thumb_path)) {
                $thumbBinary = Storage::disk($this->disk)->get($photo->thumb_path);
                $thumbZipPath = "photos/thumb_{$photo->id}.jpg";
                $zip->addFromString($thumbZipPath, $thumbBinary);
                $sha256[$thumbZipPath] = hash('sha256', $thumbBinary);
            }
        }

        // 3. Manifest file
        $manifest = [
            'format' => 'hybrid20-backup',
            'version' => 1,
            'exported_at' => Carbon::now()->toIso8601String(),
            'app_version' => '1.0.0',
            'counts' => [
                'user_settings' => count($settings),
                'exercises' => count($customExercises),
                'programs' => $programs->count(),
                'program_weeks' => $programWeeks->count(),
                'scheduled_workouts' => $scheduledWorkouts->count(),
                'scheduled_workout_exercises' => count($scheduledWorkoutExercises),
                'workouts' => $workouts->count(),
                'workout_exercises' => $workoutExercises->count(),
                'exercise_sets' => count($exerciseSets),
                'daily_activities' => count($dailyActivities),
                'body_measurements' => count($bodyMeasurements),
                'foods' => count($foods),
                'food_logs' => count($foodLogs),
                'progress_photos' => $progressPhotos->count(),
            ],
            'sha256' => $sha256,
        ];

        $manifestJson = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $zip->addFromString('manifest.json', $manifestJson);

        $zip->close();

        return $zipPath;
    }

    /**
     * Inspect and validate ZIP backup file without modifying database.
     * Returns metadata summary. (PLAN.md §8.13)
     */
    public function preview(UploadedFile|string $file): array
    {
        $realPath = $file instanceof UploadedFile ? $file->getRealPath() : $file;

        if (! file_exists($realPath)) {
            throw new InvalidArgumentException('Berkas tidak ditemukan.');
        }

        $zip = new ZipArchive();
        if ($zip->open($realPath) !== true) {
            throw new InvalidArgumentException('Berkas bukan merupakan arsip ZIP yang valid.');
        }

        $manifestContent = $zip->getFromName('manifest.json');
        if ($manifestContent === false) {
            $zip->close();
            throw new InvalidArgumentException('Berkas manifest.json tidak ditemukan di dalam arsip ZIP.');
        }

        $manifest = json_decode($manifestContent, true);
        if (! is_array($manifest)) {
            $zip->close();
            throw new InvalidArgumentException('Format manifest.json tidak valid.');
        }

        if (($manifest['format'] ?? null) !== 'hybrid20-backup') {
            $zip->close();
            throw new InvalidArgumentException('Format cadangan tidak dikenali (bukan hybrid20-backup).');
        }

        if ((int) ($manifest['version'] ?? 0) !== 1) {
            $zip->close();
            throw new InvalidArgumentException('Versi cadangan tidak didukung.');
        }

        $dataContent = $zip->getFromName('data.json');
        if ($dataContent === false) {
            $zip->close();
            throw new InvalidArgumentException('Berkas data.json tidak ditemukan di dalam arsip ZIP.');
        }

        $data = json_decode($dataContent, true);
        if (! is_array($data)) {
            $zip->close();
            throw new InvalidArgumentException('Format data.json tidak valid.');
        }

        // Verify SHA256 checksums
        $shaMap = $manifest['sha256'] ?? [];
        if (! is_array($shaMap) || empty($shaMap)) {
            $zip->close();
            throw new InvalidArgumentException('Daftar checksum sha256 kosong atau tidak valid.');
        }

        foreach ($shaMap as $internalPath => $expectedHash) {
            $content = $zip->getFromName($internalPath);
            if ($content === false) {
                $zip->close();
                throw new InvalidArgumentException("Berkas '{$internalPath}' yang tercantum pada manifest tidak ditemukan di dalam arsip.");
            }

            $actualHash = hash('sha256', $content);
            if (! hash_equals($expectedHash, $actualHash)) {
                $zip->close();
                throw new InvalidArgumentException("Integritas berkas '{$internalPath}' tidak cocok (checksum gagal).");
            }
        }

        $photoCount = 0;
        foreach (array_keys($shaMap) as $p) {
            if (str_starts_with($p, 'photos/') && ! str_starts_with($p, 'photos/thumb_')) {
                $photoCount++;
            }
        }

        $zip->close();

        return [
            'valid' => true,
            'format' => $manifest['format'],
            'version' => $manifest['version'],
            'exported_at' => $manifest['exported_at'] ?? null,
            'app_version' => $manifest['app_version'] ?? '1.0.0',
            'counts' => $manifest['counts'] ?? [],
            'photo_count' => $photoCount,
        ];
    }

    /**
     * Atomically import backup ZIP, wiping existing user data and remapping foreign keys.
     * (PLAN.md §8.13)
     */
    public function import(UploadedFile|string $file, User $user): bool
    {
        // 1. Preview & Validate
        $preview = $this->preview($file);

        $realPath = $file instanceof UploadedFile ? $file->getRealPath() : $file;
        $zip = new ZipArchive();
        if ($zip->open($realPath) !== true) {
            throw new RuntimeException('Gagal membuka arsip ZIP.');
        }

        $dataJson = $zip->getFromName('data.json');
        $data = json_decode($dataJson, true);

        // 2. Atomic Database Transaction
        DB::transaction(function () use ($zip, $data, $user) {
            // Delete user's current data in safe order
            FoodLog::where('user_id', $user->id)->delete();
            Food::where('user_id', $user->id)->delete();
            DailyActivity::where('user_id', $user->id)->delete();
            BodyMeasurement::where('user_id', $user->id)->delete();

            // Delete existing progress photos from DB and disk
            ProgressPhoto::where('user_id', $user->id)->delete();
            Storage::disk($this->disk)->deleteDirectory("progress-photos/{$user->id}");

            // Workouts & sets
            $workoutIds = Workout::where('user_id', $user->id)->pluck('id');
            $workoutExerciseIds = WorkoutExercise::whereIn('workout_id', $workoutIds)->pluck('id');
            ExerciseSet::whereIn('workout_exercise_id', $workoutExerciseIds)->delete();
            WorkoutExercise::whereIn('workout_id', $workoutIds)->delete();
            Workout::where('user_id', $user->id)->delete();

            // Program & scheduled workouts
            $programIds = Program::where('user_id', $user->id)->pluck('id');
            $programWeekIds = ProgramWeek::whereIn('program_id', $programIds)->pluck('id');
            $scheduledWorkoutIds = ScheduledWorkout::whereIn('program_week_id', $programWeekIds)->pluck('id');
            ScheduledWorkoutExercise::whereIn('scheduled_workout_id', $scheduledWorkoutIds)->delete();
            ScheduledWorkout::whereIn('program_week_id', $programWeekIds)->delete();
            ProgramWeek::whereIn('program_id', $programIds)->delete();
            Program::where('user_id', $user->id)->delete();

            // Custom exercises & settings
            Exercise::where('user_id', $user->id)->delete();
            UserSetting::where('user_id', $user->id)->delete();

            // Mapping dictionaries for remapped IDs
            $exerciseMap = [];
            $programMap = [];
            $programWeekMap = [];
            $scheduledWorkoutMap = [];
            $workoutMap = [];
            $workoutExerciseMap = [];
            $foodMap = [];

            // A. User Settings
            foreach ($data['user_settings'] ?? [] as $row) {
                $row['user_id'] = $user->id;
                unset($row['id'], $row['created_at'], $row['updated_at']);
                UserSetting::create($row);
            }

            // B. Custom Exercises
            foreach ($data['exercises'] ?? [] as $row) {
                $oldId = $row['id'];
                $row['user_id'] = $user->id;
                unset($row['id'], $row['created_at'], $row['updated_at']);
                $new = Exercise::create($row);
                $exerciseMap[$oldId] = $new->id;
            }

            // C. Programs
            foreach ($data['programs'] ?? [] as $row) {
                $oldId = $row['id'];
                $row['user_id'] = $user->id;
                unset($row['id'], $row['created_at'], $row['updated_at']);
                $new = Program::create($row);
                $programMap[$oldId] = $new->id;
            }

            // D. Program Weeks
            foreach ($data['program_weeks'] ?? [] as $row) {
                $oldId = $row['id'];
                $row['program_id'] = $programMap[$row['program_id']] ?? $row['program_id'];
                unset($row['id'], $row['created_at'], $row['updated_at']);
                $new = ProgramWeek::create($row);
                $programWeekMap[$oldId] = $new->id;
            }

            // E. Scheduled Workouts
            foreach ($data['scheduled_workouts'] ?? [] as $row) {
                $oldId = $row['id'];
                $row['program_week_id'] = $programWeekMap[$row['program_week_id']] ?? $row['program_week_id'];
                unset($row['id'], $row['created_at'], $row['updated_at']);
                $new = ScheduledWorkout::create($row);
                $scheduledWorkoutMap[$oldId] = $new->id;
            }

            // F. Scheduled Workout Exercises
            foreach ($data['scheduled_workout_exercises'] ?? [] as $row) {
                $row['scheduled_workout_id'] = $scheduledWorkoutMap[$row['scheduled_workout_id']] ?? $row['scheduled_workout_id'];
                if (isset($row['exercise_id']) && isset($exerciseMap[$row['exercise_id']])) {
                    $row['exercise_id'] = $exerciseMap[$row['exercise_id']];
                }
                unset($row['id'], $row['created_at'], $row['updated_at']);
                ScheduledWorkoutExercise::create($row);
            }

            // G. Workouts
            foreach ($data['workouts'] ?? [] as $row) {
                $oldId = $row['id'];
                $row['user_id'] = $user->id;
                if (! empty($row['scheduled_workout_id'])) {
                    $row['scheduled_workout_id'] = $scheduledWorkoutMap[$row['scheduled_workout_id']] ?? null;
                }
                unset($row['id'], $row['created_at'], $row['updated_at']);
                $new = Workout::create($row);
                $workoutMap[$oldId] = $new->id;
            }

            // H. Workout Exercises
            foreach ($data['workout_exercises'] ?? [] as $row) {
                $oldId = $row['id'];
                $row['workout_id'] = $workoutMap[$row['workout_id']] ?? $row['workout_id'];
                if (isset($row['exercise_id']) && isset($exerciseMap[$row['exercise_id']])) {
                    $row['exercise_id'] = $exerciseMap[$row['exercise_id']];
                }
                unset($row['id'], $row['created_at'], $row['updated_at']);
                $new = WorkoutExercise::create($row);
                $workoutExerciseMap[$oldId] = $new->id;
            }

            // I. Exercise Sets
            foreach ($data['exercise_sets'] ?? [] as $row) {
                $row['workout_exercise_id'] = $workoutExerciseMap[$row['workout_exercise_id']] ?? $row['workout_exercise_id'];
                unset($row['id'], $row['created_at'], $row['updated_at']);
                ExerciseSet::create($row);
            }

            // J. Daily Activities
            foreach ($data['daily_activities'] ?? [] as $row) {
                $row['user_id'] = $user->id;
                unset($row['id'], $row['created_at'], $row['updated_at']);
                DailyActivity::create($row);
            }

            // K. Body Measurements
            foreach ($data['body_measurements'] ?? [] as $row) {
                $row['user_id'] = $user->id;
                unset($row['id'], $row['created_at'], $row['updated_at']);
                BodyMeasurement::create($row);
            }

            // L. Foods
            foreach ($data['foods'] ?? [] as $row) {
                $oldId = $row['id'];
                $row['user_id'] = $user->id;
                unset($row['id'], $row['created_at'], $row['updated_at']);
                $new = Food::create($row);
                $foodMap[$oldId] = $new->id;
            }

            // M. Food Logs
            foreach ($data['food_logs'] ?? [] as $row) {
                $row['user_id'] = $user->id;
                if (! empty($row['food_id'])) {
                    $row['food_id'] = $foodMap[$row['food_id']] ?? null;
                }
                unset($row['id'], $row['created_at'], $row['updated_at']);
                FoodLog::create($row);
            }

            // N. Progress Photos & Physical Storage
            Storage::disk($this->disk)->makeDirectory("progress-photos/{$user->id}");

            foreach ($data['progress_photos'] ?? [] as $row) {
                $oldId = $row['id'];
                $photoZipPath = "photos/{$oldId}.jpg";
                $photoBinary = $zip->getFromName($photoZipPath);

                if ($photoBinary !== false) {
                    $uuid = (string) Str::uuid();
                    $mainPath = "progress-photos/{$user->id}/{$uuid}.jpg";
                    Storage::disk($this->disk)->put($mainPath, $photoBinary);

                    $thumbZipPath = "photos/thumb_{$oldId}.jpg";
                    $thumbBinary = $zip->getFromName($thumbZipPath);
                    $thumbPath = null;

                    if ($thumbBinary !== false) {
                        $thumbPath = "progress-photos/{$user->id}/thumb_{$uuid}.jpg";
                        Storage::disk($this->disk)->put($thumbPath, $thumbBinary);
                    }

                    $row['user_id'] = $user->id;
                    $row['photo_path'] = $mainPath;
                    $row['thumb_path'] = $thumbPath ?? $mainPath;
                    unset($row['id'], $row['created_at'], $row['updated_at']);
                    ProgressPhoto::create($row);
                }
            }
        });

        $zip->close();

        return true;
    }
}
