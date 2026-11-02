<?php

namespace Tests\Feature;

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
use App\Services\BackupService;
use App\Services\ProgressPhotoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class BackupAndRestoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_guest_cannot_access_backup_endpoints(): void
    {
        $this->get('/settings/backup/export')->assertRedirect('/login');
        $this->post('/settings/backup/preview')->assertRedirect('/login');
        $this->post('/settings/backup/import')->assertRedirect('/login');
    }

    public function test_export_creates_valid_zip_archive_with_manifest_and_photos(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        UserSetting::create([
            'user_id' => $user->id,
            'height_cm' => 175.0,
            'starting_weight_kg' => 74.5,
            'target_weight_kg' => 68.0,
            'daily_calorie_target' => 2200,
            'protein_target_g' => 150,
            'carbs_target_g' => 250,
            'fat_target_g' => 60,
            'daily_steps_target' => 10000,
            'timezone' => 'Asia/Jakarta',
        ]);

        $customExercise = Exercise::create([
            'user_id' => $user->id,
            'name' => 'Custom Dumbbell Press',
            'muscle_group' => 'Chest',
            'equipment' => 'Dumbbell',
            'is_custom' => true,
        ]);

        $program = Program::create([
            'user_id' => $user->id,
            'name' => 'Hybrid 20 Program',
            'start_date' => '2026-11-02',
            'duration_weeks' => 20,
            'status' => 'active',
        ]);

        $week = ProgramWeek::create([
            'program_id' => $program->id,
            'week_number' => 1,
            'calendar_position' => 1,
            'is_repeat' => false,
            'phase_label' => 'Fondasi',
        ]);

        $scheduled = ScheduledWorkout::create([
            'program_week_id' => $week->id,
            'weekday' => 1,
            'workout_type' => 'gym',
            'title' => 'Upper Body Hypertrophy',
            'status' => 'completed',
        ]);

        ScheduledWorkoutExercise::create([
            'scheduled_workout_id' => $scheduled->id,
            'exercise_id' => $customExercise->id,
            'exercise_name' => $customExercise->name,
            'target_sets' => 3,
            'target_reps' => 10,
            'sort_order' => 1,
        ]);

        $workout = Workout::create([
            'user_id' => $user->id,
            'scheduled_workout_id' => $scheduled->id,
            'workout_type' => 'gym',
            'title' => 'Upper Body Hypertrophy',
            'performed_at' => now(),
            'performed_on' => now()->toDateString(),
            'status' => 'completed',
        ]);

        $we = WorkoutExercise::create([
            'workout_id' => $workout->id,
            'exercise_id' => $customExercise->id,
            'exercise_name' => $customExercise->name,
            'sort_order' => 1,
        ]);

        ExerciseSet::create([
            'workout_exercise_id' => $we->id,
            'set_number' => 1,
            'reps' => 10,
            'weight_kg' => 24.0,
            'is_warmup' => false,
        ]);

        // Create a photo via service
        $photoService = app(ProgressPhotoService::class);
        $fakeImg = UploadedFile::fake()->image('pose_front.jpg', 600, 800);
        $photo = $photoService->storePhoto($user, $fakeImg, '2026-11-02', 'front', 'Foto awal');

        // Export
        $response = $this->get('/settings/backup/export');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/zip');

        // Verify ZIP contents directly
        $backupService = app(BackupService::class);
        $zipPath = $backupService->export($user);
        $this->assertFileExists($zipPath);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($zipPath));
        $this->assertNotFalse($zip->getFromName('manifest.json'));
        $this->assertNotFalse($zip->getFromName('data.json'));
        $this->assertNotFalse($zip->getFromName("photos/{$photo->id}.jpg"));

        $manifest = json_decode($zip->getFromName('manifest.json'), true);
        $this->assertEquals('hybrid20-backup', $manifest['format']);
        $this->assertEquals(1, $manifest['version']);
        $this->assertEquals(1, $manifest['counts']['programs']);
        $this->assertEquals(1, $manifest['counts']['workouts']);
        $this->assertEquals(1, $manifest['counts']['progress_photos']);
        $zip->close();
        @unlink($zipPath);
    }

    public function test_preview_inspects_zip_metadata_and_counts(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        UserSetting::create([
            'user_id' => $user->id,
            'timezone' => 'Asia/Jakarta',
            'daily_steps_target' => 10000,
        ]);

        $backupService = app(BackupService::class);
        $zipPath = $backupService->export($user);

        $uploaded = new UploadedFile($zipPath, 'backup.zip', 'application/zip', null, true);

        $response = $this->postJson('/settings/backup/preview', [
            'backup_file' => $uploaded,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'valid' => true,
            'format' => 'hybrid20-backup',
            'version' => 1,
            'counts' => [
                'user_settings' => 1,
            ],
        ]);

        @unlink($zipPath);
    }

    public function test_preview_rejects_corrupted_or_non_zip_file(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $fakeText = UploadedFile::fake()->create('corrupt.zip', 10, 'application/zip');

        $response = $this->postJson('/settings/backup/preview', [
            'backup_file' => $fakeText,
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['error']);
    }

    public function test_preview_rejects_tampered_checksum(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        UserSetting::create(['user_id' => $user->id, 'timezone' => 'Asia/Jakarta']);
        $backupService = app(BackupService::class);
        $zipPath = $backupService->export($user);

        // Tamper data.json inside ZIP
        $zip = new ZipArchive();
        $zip->open($zipPath);
        $zip->addFromString('data.json', json_encode(['tampered' => true]));
        $zip->close();

        $uploaded = new UploadedFile($zipPath, 'tampered.zip', 'application/zip', null, true);
        $response = $this->postJson('/settings/backup/preview', [
            'backup_file' => $uploaded,
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['error' => "Integritas berkas 'data.json' tidak cocok (checksum gagal)."]);

        @unlink($zipPath);
    }

    public function test_import_requires_explicit_confirmation_checkbox(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        UserSetting::create(['user_id' => $user->id, 'timezone' => 'Asia/Jakarta']);
        $backupService = app(BackupService::class);
        $zipPath = $backupService->export($user);

        $uploaded = new UploadedFile($zipPath, 'backup.zip', 'application/zip', null, true);

        // Send without confirm_replace
        $response = $this->post('/settings/backup/import', [
            'backup_file' => $uploaded,
        ]);

        $response->assertSessionHasErrors(['confirm_replace']);
        @unlink($zipPath);
    }

    public function test_round_trip_export_and_import_preserves_all_data_identically(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // 1. Populate User A with rich data
        UserSetting::create([
            'user_id' => $userA->id,
            'height_cm' => 180.0,
            'starting_weight_kg' => 82.5,
            'target_weight_kg' => 75.0,
            'daily_calorie_target' => 2400,
            'protein_target_g' => 160,
            'carbs_target_g' => 260,
            'fat_target_g' => 65,
            'daily_steps_target' => 12000,
            'timezone' => 'Asia/Jakarta',
        ]);

        $customExercise = Exercise::create([
            'user_id' => $userA->id,
            'name' => 'Incline DB Bench Press',
            'muscle_group' => 'Chest',
            'equipment' => 'Dumbbell',
            'is_custom' => true,
        ]);

        $program = Program::create([
            'user_id' => $userA->id,
            'name' => 'Hybrid 20 v1',
            'start_date' => '2026-11-02',
            'duration_weeks' => 20,
            'status' => 'active',
        ]);

        $week = ProgramWeek::create([
            'program_id' => $program->id,
            'week_number' => 1,
            'calendar_position' => 1,
            'is_repeat' => false,
            'phase_label' => 'Fondasi',
        ]);

        $scheduled = ScheduledWorkout::create([
            'program_week_id' => $week->id,
            'weekday' => 1,
            'workout_type' => 'gym',
            'title' => 'Push A',
            'status' => 'completed',
        ]);

        ScheduledWorkoutExercise::create([
            'scheduled_workout_id' => $scheduled->id,
            'exercise_id' => $customExercise->id,
            'exercise_name' => $customExercise->name,
            'target_sets' => 4,
            'target_reps' => 8,
            'sort_order' => 1,
        ]);

        $workout = Workout::create([
            'user_id' => $userA->id,
            'scheduled_workout_id' => $scheduled->id,
            'workout_type' => 'gym',
            'title' => 'Push A Sesi',
            'performed_at' => now(),
            'performed_on' => now()->toDateString(),
            'status' => 'completed',
        ]);

        $we = WorkoutExercise::create([
            'workout_id' => $workout->id,
            'exercise_id' => $customExercise->id,
            'exercise_name' => $customExercise->name,
            'sort_order' => 1,
        ]);

        ExerciseSet::create([
            'workout_exercise_id' => $we->id,
            'set_number' => 1,
            'reps' => 8,
            'weight_kg' => 28.0,
            'is_warmup' => false,
        ]);

        DailyActivity::create([
            'user_id' => $userA->id,
            'activity_date' => '2026-11-02',
            'steps' => 11450,
        ]);

        BodyMeasurement::create([
            'user_id' => $userA->id,
            'measured_at' => '2026-11-02',
            'weight_kg' => 82.2,
            'waist_cm' => 86.5,
        ]);

        $food = Food::create([
            'user_id' => $userA->id,
            'name' => 'Dada Ayam Rebus',
            'serving_name' => 'Potong',
            'serving_size_g' => 100.0,
            'calories_kcal' => 165.0,
            'protein_g' => 31.0,
            'carbs_g' => 0.0,
            'fat_g' => 3.6,
        ]);

        FoodLog::create([
            'user_id' => $userA->id,
            'food_id' => $food->id,
            'logged_at' => now(),
            'logged_on' => now()->toDateString(),
            'meal_type' => 'lunch',
            'food_name_snapshot' => $food->name,
            'serving_name_snapshot' => $food->serving_name,
            'serving_size_g_snapshot' => $food->serving_size_g,
            'servings' => 2.0,
            'calories_kcal' => 330.0,
            'protein_g' => 62.0,
            'carbs_g' => 0.0,
            'fat_g' => 7.2,
        ]);

        $photoService = app(ProgressPhotoService::class);
        $fakeImg = UploadedFile::fake()->image('test_front.jpg', 600, 800);
        $photoService->storePhoto($userA, $fakeImg, '2026-11-02', 'front', 'Catatan foto awal');

        // 2. Export User A's data
        $backupService = app(BackupService::class);
        $zipPath = $backupService->export($userA);

        // 3. Import into User B
        $this->actingAs($userB);
        $uploaded = new UploadedFile($zipPath, 'user_a_backup.zip', 'application/zip', null, true);

        $response = $this->post('/settings/backup/import', [
            'backup_file' => $uploaded,
            'confirm_replace' => '1',
        ]);

        $response->assertRedirect(route('settings.edit'));
        $response->assertSessionHas('success');

        // 4. Verify User B has identical restored data with properly remapped IDs
        $userBSetting = UserSetting::where('user_id', $userB->id)->first();
        $this->assertNotNull($userBSetting);
        $this->assertEquals(180.0, (float)$userBSetting->height_cm);
        $this->assertEquals(82.5, (float)$userBSetting->starting_weight_kg);
        $this->assertEquals(12000, $userBSetting->daily_steps_target);

        $userBPrograms = Program::where('user_id', $userB->id)->get();
        $this->assertCount(1, $userBPrograms);
        $newProgram = $userBPrograms->first();
        $this->assertEquals('Hybrid 20 v1', $newProgram->name);

        $userBWeeks = ProgramWeek::where('program_id', $newProgram->id)->get();
        $this->assertCount(1, $userBWeeks);
        $newWeek = $userBWeeks->first();

        $userBScheduled = ScheduledWorkout::where('program_week_id', $newWeek->id)->get();
        $this->assertCount(1, $userBScheduled);
        $newScheduled = $userBScheduled->first();
        $this->assertEquals('Push A', $newScheduled->title);

        $userBWorkouts = Workout::where('user_id', $userB->id)->get();
        $this->assertCount(1, $userBWorkouts);
        $newWorkout = $userBWorkouts->first();
        $this->assertEquals($newScheduled->id, $newWorkout->scheduled_workout_id);

        $userBSets = ExerciseSet::whereHas('workoutExercise', fn($q) => $q->where('workout_id', $newWorkout->id))->get();
        $this->assertCount(1, $userBSets);
        $this->assertEquals(28.0, (float)$userBSets->first()->weight_kg);

        $userBSteps = DailyActivity::where('user_id', $userB->id)->first();
        $this->assertEquals(11450, $userBSteps->steps);

        $userBMeasure = BodyMeasurement::where('user_id', $userB->id)->first();
        $this->assertEquals(82.2, (float)$userBMeasure->weight_kg);

        $userBFood = Food::where('user_id', $userB->id)->first();
        $this->assertEquals('Dada Ayam Rebus', $userBFood->name);

        $userBLog = FoodLog::where('user_id', $userB->id)->first();
        $this->assertEquals($userBFood->id, $userBLog->food_id);
        $this->assertEquals(330.0, (float)$userBLog->calories_kcal);

        $userBPhotos = ProgressPhoto::where('user_id', $userB->id)->get();
        $this->assertCount(1, $userBPhotos);
        $newPhoto = $userBPhotos->first();
        $this->assertStringContainsString("progress-photos/{$userB->id}/", $newPhoto->photo_path);
        $this->assertTrue(Storage::disk('local')->exists($newPhoto->photo_path));

        @unlink($zipPath);
    }

    public function test_import_rejects_corrupted_archive(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $fakeFile = UploadedFile::fake()->create('not_really_a_zip.zip', 50, 'application/zip');

        $response = $this->post('/settings/backup/import', [
            'backup_file' => $fakeFile,
            'confirm_replace' => '1',
        ]);

        $response->assertSessionHasErrors(['backup_file']);
    }

    public function test_import_failure_rolls_back_atomically(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // User initially has a setting and a daily activity
        UserSetting::create([
            'user_id' => $user->id,
            'timezone' => 'Asia/Jakarta',
            'daily_steps_target' => 8888,
        ]);
        DailyActivity::create([
            'user_id' => $user->id,
            'activity_date' => '2026-11-01',
            'steps' => 5000,
        ]);

        // Create a ZIP with valid manifest and checksums, but invalid foreign key or broken data that triggers an exception during insert
        $tempDir = storage_path('app/private/temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $zipPath = $tempDir . '/invalid_data_backup.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Data contains an invalid row for scheduled_workout_exercises referencing non-existent program_week
        $data = [
            'user_settings' => [
                ['height_cm' => 170]
            ],
            'scheduled_workout_exercises' => [
                ['scheduled_workout_id' => 999999, 'exercise_id' => 999999, 'target_sets' => 'invalid_type_array']
            ]
        ];
        $dataJson = json_encode($data);
        $zip->addFromString('data.json', $dataJson);

        $manifest = [
            'format' => 'hybrid20-backup',
            'version' => 1,
            'exported_at' => now()->toIso8601String(),
            'counts' => ['user_settings' => 1],
            'sha256' => [
                'data.json' => hash('sha256', $dataJson),
            ],
        ];
        $zip->addFromString('manifest.json', json_encode($manifest));
        $zip->close();

        $uploaded = new UploadedFile($zipPath, 'invalid.zip', 'application/zip', null, true);
        $response = $this->post('/settings/backup/import', [
            'backup_file' => $uploaded,
            'confirm_replace' => '1',
        ]);

        $response->assertSessionHasErrors(['backup_file']);

        // Assert that the original user setting and steps are still intact!
        $currentSetting = UserSetting::where('user_id', $user->id)->first();
        $this->assertNotNull($currentSetting);
        $this->assertEquals(8888, $currentSetting->daily_steps_target);

        $currentActivity = DailyActivity::where('user_id', $user->id)->first();
        $this->assertNotNull($currentActivity);
        $this->assertEquals(5000, $currentActivity->steps);

        @unlink($zipPath);
    }
}
