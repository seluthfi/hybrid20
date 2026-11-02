<?php

namespace Tests\Feature;

use App\Models\DailyActivity;
use App\Models\Exercise;
use App\Models\Food;
use App\Models\FoodLog;
use App\Models\Program;
use App\Models\ProgramWeek;
use App\Models\ProgressPhoto;
use App\Models\ScheduledWorkout;
use App\Models\User;
use App\Models\UserMeasurement;
use App\Models\UserSetting;
use App\Models\Workout;
use App\Models\WorkoutExercise;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FunctionalAuditPhase1To6Test extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected UserSetting $settings;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'audit@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $this->settings = UserSetting::factory()->create([
            'user_id' => $this->user->id,
            'timezone' => 'Asia/Jakarta',
            'daily_steps_target' => 8000,
            'daily_calorie_target' => 2000,
            'protein_target_g' => 150,
            'carbs_target_g' => 200,
            'fat_target_g' => 60,
        ]);
    }

    /**
     * 1. GET routes return 200 on completely empty database (no program, no workouts, no foods).
     */
    public function test_get_routes_return_200_on_empty_database(): void
    {
        $this->actingAs($this->user);

        $routes = [
            'today',
            'workouts.index',
            'body.index',
            'foods.index',
            'foods.create',
            'food-logs.index',
            'food-logs.create',
            'progress-photos.index',
            'progress-photos.compare',
            'plan.show',
            'settings.edit',
            'charts.weight',
            'charts.run-weekly',
            'charts.exercise-history',
        ];

        foreach ($routes as $route) {
            $response = $this->get(route($route));
            $response->assertStatus(200);
        }

        // Test workouts create gym & run
        $this->get(route('workouts.create', ['type' => 'gym']))->assertStatus(200);
        $this->get(route('workouts.create', ['type' => 'run']))->assertStatus(200);
    }

    /**
     * 2. GET routes return 200 on populated database.
     */
    public function test_get_routes_return_200_on_populated_database(): void
    {
        $this->actingAs($this->user);

        $program = Program::factory()->create([
            'user_id' => $this->user->id,
            'start_date' => Carbon::now()->startOfWeek(Carbon::MONDAY),
        ]);

        $week = ProgramWeek::factory()->create([
            'program_id' => $program->id,
            'week_number' => 1,
        ]);

        $scheduled = ScheduledWorkout::factory()->create([
            'program_week_id' => $week->id,
            'workout_type' => 'gym',
            'weekday' => 1,
            'status' => 'planned',
        ]);

        $workout = Workout::factory()->create([
            'user_id' => $this->user->id,
            'workout_type' => 'gym',
            'performed_on' => Carbon::today()->toDateString(),
        ]);

        $food = Food::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Ayam Panggang',
            'calories_kcal' => 250,
        ]);

        $foodLog = FoodLog::factory()->create([
            'user_id' => $this->user->id,
            'food_id' => $food->id,
            'logged_on' => Carbon::today()->toDateString(),
            'servings' => 1.5,
            'calories_kcal' => 375,
        ]);

        $measurement = \App\Models\BodyMeasurement::factory()->create([
            'user_id' => $this->user->id,
            'measured_at' => Carbon::today()->toDateString(),
            'weight_kg' => 72.5,
        ]);

        $routesWithParams = [
            ['workouts.show', ['workout' => $workout->id]],
            ['workouts.edit', ['workout' => $workout->id]],
            ['foods.edit', ['food' => $food->id]],
            ['plan.show', ['calendar_position' => 1]],
        ];

        foreach ($routesWithParams as [$route, $params]) {
            $response = $this->get(route($route, $params));
            $response->assertStatus(200);
        }
    }

    /**
     * 3. Food CRUD endpoints (success and validation failure).
     */
    public function test_food_crud_and_validation(): void
    {
        $this->actingAs($this->user);

        // POST success
        $res = $this->post(route('foods.store'), [
            'name' => 'Nasi Merah',
            'serving_name' => '1 mangkuk',
            'serving_size_g' => '150,5',
            'calories_kcal' => '210,0',
            'protein_g' => '4,5',
            'carbs_g' => '45,2',
            'fat_g' => '1,8',
            'is_favorite' => '1',
        ]);
        $res->assertRedirect(route('foods.index'));
        $this->assertDatabaseHas('foods', ['name' => 'Nasi Merah', 'calories_kcal' => 210.0]);

        $food = Food::where('name', 'Nasi Merah')->first();

        // Validation fail on POST
        $resFail = $this->post(route('foods.store'), [
            'name' => '', // required
            'calories_kcal' => 'bukan-angka',
        ]);
        $resFail->assertSessionHasErrors(['name', 'calories_kcal']);

        // PUT success
        $resPut = $this->put(route('foods.update', $food->id), [
            'name' => 'Nasi Merah Organik',
            'serving_name' => '1 mangkuk',
            'serving_size_g' => '150',
            'calories_kcal' => '220',
            'protein_g' => '5',
            'carbs_g' => '46',
            'fat_g' => '2',
        ]);
        $resPut->assertRedirect(route('foods.index'));
        $this->assertDatabaseHas('foods', ['id' => $food->id, 'name' => 'Nasi Merah Organik']);

        // DELETE success
        $resDel = $this->delete(route('foods.destroy', $food->id));
        $resDel->assertRedirect(route('foods.index'));
        $this->assertDatabaseMissing('foods', ['id' => $food->id]);
    }

    /**
     * 4. Food log endpoints (success and validation failure).
     */
    public function test_food_log_crud_and_validation(): void
    {
        $this->actingAs($this->user);

        $food = Food::factory()->create([
            'user_id' => $this->user->id,
            'calories_kcal' => 100,
            'protein_g' => 10,
        ]);

        // POST success
        $res = $this->post(route('food-logs.store'), [
            'food_id' => $food->id,
            'meal_type' => 'breakfast',
            'logged_on' => Carbon::today()->toDateString(),
            'servings' => '2,5',
        ]);
        $res->assertRedirect();
        $this->assertDatabaseHas('food_logs', ['food_id' => $food->id, 'servings' => 2.5, 'calories_kcal' => 250]);

        $foodLog = FoodLog::where('food_id', $food->id)->first();

        // Validation failure
        $fail = $this->post(route('food-logs.store'), [
            'food_id' => 999999,
            'meal_type' => 'invalid_meal',
            'logged_on' => 'invalid_date',
            'servings' => '0',
        ]);
        $fail->assertSessionHasErrors(['food_id', 'meal_type', 'logged_on', 'servings']);

        // PUT success
        $put = $this->put(route('food-logs.update', $foodLog->id), [
            'servings' => '3',
            'meal_type' => 'lunch',
        ]);
        $put->assertRedirect();
        $this->assertDatabaseHas('food_logs', ['id' => $foodLog->id, 'servings' => 3, 'meal_type' => 'lunch']);

        // DELETE success
        $del = $this->delete(route('food-logs.destroy', $foodLog->id));
        $del->assertRedirect();
        $this->assertDatabaseMissing('food_logs', ['id' => $foodLog->id]);
    }

    /**
     * 5. Body measurements upsert and daily steps upsert.
     */
    public function test_measurements_and_steps_upsert(): void
    {
        $this->actingAs($this->user);
        $date = Carbon::today()->toDateString();

        // Measurement upsert
        $resWeight = $this->put(route('measurements.upsert', ['date' => $date]), [
            'weight_kg' => '73,8',
            'waist_cm' => '82,5',
        ]);
        $resWeight->assertRedirect();
        $this->assertDatabaseHas('body_measurements', ['user_id' => $this->user->id, 'weight_kg' => 73.8, 'waist_cm' => 82.5]);

        // Measurement delete
        $delWeight = $this->delete(route('measurements.destroy', ['date' => $date]));
        $delWeight->assertRedirect();
        $this->assertDatabaseMissing('body_measurements', ['user_id' => $this->user->id, 'measured_at' => $date]);

        // Steps upsert
        $resSteps = $this->put(route('daily-activities.upsert', ['date' => $date]), [
            'steps' => '12.450',
        ]);
        $resSteps->assertRedirect();
        $this->assertDatabaseHas('daily_activities', ['user_id' => $this->user->id, 'steps' => 12450]);
    }

    /**
     * 6. Cross-user access denial (403/404).
     */
    public function test_cross_user_access_is_forbidden(): void
    {
        $otherUser = User::factory()->create();
        $otherWorkout = Workout::factory()->create(['user_id' => $otherUser->id]);
        $otherFood = Food::factory()->create(['user_id' => $otherUser->id]);
        $otherPhoto = ProgressPhoto::factory()->create(['user_id' => $otherUser->id, 'photo_path' => 'progress-photos/test.jpg']);

        $this->actingAs($this->user);

        // Cannot view or edit other user's workout (403 or 404)
        $res = $this->get(route('workouts.show', $otherWorkout->id));
        $this->assertTrue(in_array($res->status(), [403, 404]));

        $res = $this->get(route('workouts.edit', $otherWorkout->id));
        $this->assertTrue(in_array($res->status(), [403, 404]));

        $res = $this->delete(route('workouts.destroy', $otherWorkout->id));
        $this->assertTrue(in_array($res->status(), [403, 404]));

        // Cannot edit or delete other user's food (403 or 404)
        $res = $this->get(route('foods.edit', $otherFood->id));
        $this->assertTrue(in_array($res->status(), [403, 404]));

        $res = $this->put(route('foods.update', $otherFood->id), ['name' => 'Hacked', 'calories_kcal' => 100]);
        $this->assertTrue(in_array($res->status(), [403, 404]));

        $res = $this->delete(route('foods.destroy', $otherFood->id));
        $this->assertTrue(in_array($res->status(), [403, 404]));

        // Cannot view other user's photo (403 or 404)
        $res = $this->get(route('progress-photos.image', $otherPhoto->id));
        $this->assertTrue(in_array($res->status(), [403, 404]));

        $res = $this->get(route('progress-photos.thumb', $otherPhoto->id));
        $this->assertTrue(in_array($res->status(), [403, 404]));

        $res = $this->delete(route('progress-photos.destroy', $otherPhoto->id));
        $this->assertTrue(in_array($res->status(), [403, 404]));
    }

    /**
     * 7. Photo upload: private disk, authorized route, EXIF GPS stripped.
     */
    public function test_photo_upload_stores_in_private_disk_and_strips_exif(): void
    {
        Storage::fake('local');
        $this->actingAs($this->user);

        // Create a real JPEG with EXIF header containing dummy data
        $imageResource = imagecreatetruecolor(100, 100);
        $color = imagecolorallocate($imageResource, 200, 100, 50);
        imagefilledrectangle($imageResource, 0, 0, 100, 100, $color);
        
        ob_start();
        imagejpeg($imageResource);
        $imageData = ob_get_clean();
        imagedestroy($imageResource);

        $file = UploadedFile::fake()->createWithContent('photo_with_exif.jpg', $imageData);

        $response = $this->post(route('progress-photos.store'), [
            'photo' => $file,
            'taken_at' => Carbon::today()->toDateString(),
            'pose_label' => 'front',
        ]);

        $response->assertRedirect(route('progress-photos.index'));
        $this->assertDatabaseHas('progress_photos', [
            'user_id' => $this->user->id,
            'pose_label' => 'front',
        ]);

        $photo = ProgressPhoto::where('user_id', $this->user->id)->first();
        $this->assertNotNull($photo);

        // Verify file stored in private local disk
        Storage::disk('local')->assertExists($photo->photo_path);

        // Verify EXIF cannot read GPS from re-encoded image
        $savedContent = Storage::disk('local')->get($photo->photo_path);
        $tempPath = tempnam(sys_get_temp_dir(), 'test_photo');
        file_put_contents($tempPath, $savedContent);

        if (function_exists('exif_read_data')) {
            $exif = @exif_read_data($tempPath);
            $this->assertFalse(isset($exif['GPS']) || isset($exif['GPSLatitude']), 'EXIF GPS must be stripped');
        }
        @unlink($tempPath);
    }
}
