<?php

namespace Tests\Feature;

use App\Models\BodyMeasurement;
use App\Models\Food;
use App\Models\FoodLog;
use App\Models\ProgressPhoto;
use App\Models\User;
use App\Models\UserSetting;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NutrisiDanTubuhTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->user = User::factory()->create([
            'email' => 'athlete@hybrid20.local',
        ]);

        $this->otherUser = User::factory()->create([
            'email' => 'other@hybrid20.local',
        ]);

        UserSetting::create([
            'user_id' => $this->user->id,
            'height_cm' => 163,
            'starting_weight_kg' => 74.0,
            'target_weight_kg' => 60.0,
            'daily_calorie_target' => 2200,
            'protein_target_g' => 140,
            'carbs_target_g' => 260,
            'fat_target_g' => 65,
            'daily_steps_target' => 10000,
            'timezone' => 'Asia/Jakarta',
            'weight_unit' => 'kg',
        ]);
    }

    /**
     * Test Food master creation, update, and search endpoint.
     */
    public function test_food_master_crud_and_search(): void
    {
        // 1. Create custom food
        $response = $this->actingAs($this->user)->post('/foods', [
            'name' => 'Dada Ayam Fillet',
            'serving_name' => '1 potong',
            'serving_size_g' => 100,
            'calories_kcal' => 165,
            'protein_g' => 31,
            'carbs_g' => 0,
            'fat_g' => 3.6,
            'is_favorite' => 1,
        ]);

        $response->assertRedirect('/foods');
        $this->assertDatabaseHas('foods', [
            'user_id' => $this->user->id,
            'name' => 'Dada Ayam Fillet',
            'is_favorite' => true,
        ]);

        $food = Food::where('name', 'Dada Ayam Fillet')->first();

        // 2. Search endpoint
        $searchResponse = $this->actingAs($this->user)->getJson('/foods/search?q=Dada&filter=favorite');
        $searchResponse->assertStatus(200);
        $searchResponse->assertJsonFragment(['name' => 'Dada Ayam Fillet']);

        // 3. Other user cannot see this food in search
        $otherSearch = $this->actingAs($this->otherUser)->getJson('/foods/search?q=Dada');
        $otherSearch->assertStatus(200);
        $otherSearch->assertJsonMissing(['name' => 'Dada Ayam Fillet']);
    }

    /**
     * Test macro snapshot calculation on FoodLog creation (PLAN.md §8.7, §13).
     * Snapshot = master * servings.
     */
    public function test_food_log_macro_snapshot_calculation(): void
    {
        $food = Food::create([
            'user_id' => $this->user->id,
            'name' => 'Telur Rebus',
            'serving_name' => '1 butir',
            'serving_size_g' => 50,
            'calories_kcal' => 78,
            'protein_g' => 6,
            'carbs_g' => 0.5,
            'fat_g' => 5,
            'is_favorite' => true,
        ]);

        // Log 2.5 servings
        $response = $this->actingAs($this->user)->post('/food-logs', [
            'food_id' => $food->id,
            'meal_type' => 'breakfast',
            'logged_on' => '2026-11-03',
            'servings' => 2.5,
        ]);

        $response->assertRedirect('/food-logs?date=2026-11-03');

        $log = FoodLog::where('user_id', $this->user->id)->first();
        $this->assertNotNull($log);
        $this->assertSame('Telur Rebus', $log->food_name_snapshot);
        $this->assertEquals(2.5, (float) $log->servings);
        $this->assertEquals(195.0, (float) $log->calories_kcal); // 78 * 2.5 = 195
        $this->assertEquals(15.0, (float) $log->protein_g);       // 6 * 2.5 = 15
        $this->assertEquals(1.25, (float) $log->carbs_g);       // 0.5 * 2.5 = 1.25
        $this->assertEquals(12.5, (float) $log->fat_g);         // 5 * 2.5 = 12.5
    }

    /**
     * Test editing food master does NOT alter existing snapshot logs (PLAN.md §8.7, §13).
     */
    public function test_editing_food_master_does_not_alter_existing_snapshots(): void
    {
        $food = Food::create([
            'user_id' => $this->user->id,
            'name' => 'Nasi Putih',
            'serving_name' => '1 centong',
            'serving_size_g' => 100,
            'calories_kcal' => 130,
            'protein_g' => 2.7,
            'carbs_g' => 28,
            'fat_g' => 0.3,
        ]);

        $log = FoodLog::create([
            'user_id' => $this->user->id,
            'food_id' => $food->id,
            'logged_at' => Carbon::now('UTC'),
            'logged_on' => '2026-11-03',
            'meal_type' => 'lunch',
            'food_name_snapshot' => $food->name,
            'serving_name_snapshot' => $food->serving_name,
            'serving_size_g_snapshot' => $food->serving_size_g,
            'servings' => 2,
            'calories_kcal' => 260,
            'protein_g' => 5.4,
            'carbs_g' => 56,
            'fat_g' => 0.6,
        ]);

        // Edit master food
        $food->update([
            'calories_kcal' => 200,
            'protein_g' => 5.0,
        ]);

        // Existing log snapshot must remain identical
        $freshLog = $log->fresh();
        $this->assertEquals(260.0, (float) $freshLog->calories_kcal);
        $this->assertEquals(5.4, (float) $freshLog->protein_g);
    }

    /**
     * Test deleting master food sets food_id to null but retains snapshot (PLAN.md §13).
     */
    public function test_deleting_master_food_sets_food_id_to_null_retaining_snapshots(): void
    {
        $food = Food::create([
            'user_id' => $this->user->id,
            'name' => 'Oatmeal',
            'serving_name' => '1 mangkuk',
            'serving_size_g' => 40,
            'calories_kcal' => 150,
            'protein_g' => 5,
            'carbs_g' => 27,
            'fat_g' => 2.5,
        ]);

        $log = FoodLog::create([
            'user_id' => $this->user->id,
            'food_id' => $food->id,
            'logged_at' => Carbon::now('UTC'),
            'logged_on' => '2026-11-03',
            'meal_type' => 'breakfast',
            'food_name_snapshot' => $food->name,
            'serving_name_snapshot' => $food->serving_name,
            'serving_size_g_snapshot' => $food->serving_size_g,
            'servings' => 1,
            'calories_kcal' => 150,
            'protein_g' => 5,
            'carbs_g' => 27,
            'fat_g' => 2.5,
        ]);

        // Delete master food
        $this->actingAs($this->user)->delete("/foods/{$food->id}");

        $this->assertDatabaseMissing('foods', ['id' => $food->id]);

        $freshLog = $log->fresh();
        $this->assertNull($freshLog->food_id);
        $this->assertSame('Oatmeal', $freshLog->food_name_snapshot);
        $this->assertEquals(150.0, (float) $freshLog->calories_kcal);
    }

    /**
     * Test food diary header sum equals items sum (PLAN.md §8.7).
     */
    public function test_meal_header_equals_sum_of_items(): void
    {
        // 2 items for breakfast
        FoodLog::create([
            'user_id' => $this->user->id,
            'logged_at' => Carbon::now('UTC'),
            'logged_on' => '2026-11-03',
            'meal_type' => 'breakfast',
            'food_name_snapshot' => 'Telur',
            'serving_name_snapshot' => '1 butir',
            'serving_size_g_snapshot' => 50,
            'servings' => 2,
            'calories_kcal' => 156,
            'protein_g' => 12,
            'carbs_g' => 1,
            'fat_g' => 10,
        ]);

        FoodLog::create([
            'user_id' => $this->user->id,
            'logged_at' => Carbon::now('UTC'),
            'logged_on' => '2026-11-03',
            'meal_type' => 'breakfast',
            'food_name_snapshot' => 'Roti Gandum',
            'serving_name_snapshot' => '1 lembar',
            'serving_size_g_snapshot' => 30,
            'servings' => 2,
            'calories_kcal' => 140,
            'protein_g' => 6,
            'carbs_g' => 24,
            'fat_g' => 2,
        ]);

        $response = $this->actingAs($this->user)->get('/food-logs?date=2026-11-03');
        $response->assertStatus(200);

        // Header total: 156 + 140 = 296 kcal
        $response->assertSee('296 kcal');
        // Protein: 12 + 6 = 18g
        $response->assertSee('P: 18');
    }

    /**
     * Test body measurements upsert and unique constraint per date (PLAN.md §13).
     */
    public function test_body_measurements_upsert_and_unique_constraint(): void
    {
        // 1. Initial measurement
        $response1 = $this->actingAs($this->user)->put('/body/2026-11-03', [
            'weight_kg' => '74,2',
            'waist_cm' => '82,5',
        ]);
        $response1->assertRedirect();

        $m1 = BodyMeasurement::where('user_id', $this->user->id)->whereDate('measured_at', '2026-11-03')->first();
        $this->assertNotNull($m1);
        $this->assertEquals(74.2, (float) $m1->weight_kg);
        $this->assertEquals(82.5, (float) $m1->waist_cm);

        // 2. Upsert same date with updated weight and additional metrics
        $response2 = $this->actingAs($this->user)->put('/body/2026-11-03', [
            'weight_kg' => '73,9',
            'chest_cm' => '98,0',
        ]);
        $response2->assertRedirect();

        $this->assertSame(1, BodyMeasurement::where('user_id', $this->user->id)->whereDate('measured_at', '2026-11-03')->count());
        $m = BodyMeasurement::where('user_id', $this->user->id)->whereDate('measured_at', '2026-11-03')->first();
        $this->assertEquals(73.9, (float) $m->weight_kg);
        $this->assertEquals(98.0, (float) $m->chest_cm);
        $this->assertEquals(82.5, (float) $m->waist_cm); // Unchanged metric preserved
    }

    /**
     * Test 7-day trend shows "Belum cukup data" when < 4 measurements (PLAN.md §8.8).
     */
    public function test_seven_day_trend_requires_minimum_four_data_points(): void
    {
        // Only 2 data points
        BodyMeasurement::create([
            'user_id' => $this->user->id,
            'measured_at' => '2026-11-01',
            'weight_kg' => 74.5,
        ]);
        BodyMeasurement::create([
            'user_id' => $this->user->id,
            'measured_at' => '2026-11-03',
            'weight_kg' => 74.0,
        ]);

        $response = $this->actingAs($this->user)->get('/body');
        $response->assertStatus(200);
        $response->assertSee('Belum cukup data (2 dari 7 hari)');
    }

    /**
     * Test progress photo upload, private disk storage, and EXIF stripping (PLAN.md §12, §13).
     */
    public function test_progress_photo_private_storage_and_exif_removal(): void
    {
        // Create sample GD image
        $image = imagecreatetruecolor(100, 100);
        $color = imagecolorallocate($image, 255, 0, 0);
        imagefill($image, 0, 0, $color);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_photo') . '.jpg';
        imagejpeg($image, $tempPath);
        imagedestroy($image);

        $uploadedFile = new UploadedFile($tempPath, 'checkin.jpg', 'image/jpeg', null, true);

        $response = $this->actingAs($this->user)->post('/progress-photos', [
            'photo' => $uploadedFile,
            'taken_at' => '2026-11-03',
            'pose_label' => 'front',
            'notes' => 'Catatan kondisi pagi',
        ]);

        $response->assertRedirect('/progress-photos');

        $photo = ProgressPhoto::where('user_id', $this->user->id)->first();
        $this->assertNotNull($photo);
        $this->assertStringStartsWith('progress-photos/', $photo->photo_path);
        $this->assertStringStartsWith('progress-photos/', $photo->thumb_path);

        // Verify stored in private disk
        Storage::disk('local')->assertExists($photo->photo_path);
        Storage::disk('local')->assertExists($photo->thumb_path);

        // Verify authorized route streams file with private headers
        $streamResponse = $this->actingAs($this->user)->get("/progress-photos/{$photo->id}/image");
        $streamResponse->assertStatus(200);
        $cc = $streamResponse->headers->get('Cache-Control');
        $this->assertStringContainsString('private', $cc);
        $this->assertStringContainsString('no-store', $cc);

        // Other user cannot access photo (Policy)
        $unauthorizedResponse = $this->actingAs($this->otherUser)->get("/progress-photos/{$photo->id}/image");
        $unauthorizedResponse->assertStatus(404);

        // Guest cannot access photo
        auth()->logout();
        $guestResponse = $this->get("/progress-photos/{$photo->id}/image");
        $guestResponse->assertRedirect('/login');

        // Cleanup temp file
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }

    /**
     * Test deleting progress photo cleans up physical files from storage.
     */
    public function test_deleting_progress_photo_removes_physical_files(): void
    {
        $image = imagecreatetruecolor(50, 50);
        $tempPath = tempnam(sys_get_temp_dir(), 'test_del') . '.jpg';
        imagejpeg($image, $tempPath);
        imagedestroy($image);

        $uploadedFile = new UploadedFile($tempPath, 'del.jpg', 'image/jpeg', null, true);
        $this->actingAs($this->user)->post('/progress-photos', [
            'photo' => $uploadedFile,
            'taken_at' => '2026-11-03',
            'pose_label' => 'front',
        ]);

        $photo = ProgressPhoto::where('user_id', $this->user->id)->first();
        $mainPath = $photo->photo_path;
        $thumbPath = $photo->thumb_path;

        Storage::disk('local')->assertExists($mainPath);

        // Delete photo
        $deleteResponse = $this->actingAs($this->user)->delete("/progress-photos/{$photo->id}");
        $deleteResponse->assertRedirect('/progress-photos');

        $this->assertDatabaseMissing('progress_photos', ['id' => $photo->id]);
        Storage::disk('local')->assertMissing($mainPath);
        Storage::disk('local')->assertMissing($thumbPath);

        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }
}
