<?php

namespace Tests\Feature;

use App\Models\BodyMeasurement;
use App\Models\Exercise;
use App\Models\Food;
use App\Models\FoodLog;
use App\Models\Program;
use App\Models\User;
use App\Models\UserSetting;
use App\Models\Workout;
use App\Models\WorkoutExercise;
use App\Models\ExerciseSet;
use App\Services\ProgramDateService;
use App\Services\ProgramGenerator;
use Carbon\Carbon;
use Database\Seeders\DefaultExerciseSeeder;
use Database\Seeders\GymTemplateSeeder;
use Database\Seeders\RunningPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardDanGrafikTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultExerciseSeeder::class);

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

        UserSetting::create([
            'user_id' => $this->otherUser->id,
            'timezone' => 'Asia/Jakarta',
        ]);
    }

    /**
     * Unauthenticated access to /charts/* redirects to /login.
     */
    public function test_charts_endpoints_require_authentication(): void
    {
        $this->get('/charts/weight')->assertRedirect('/login');
        $this->get('/charts/run-weekly')->assertRedirect('/login');
        $this->get('/charts/exercise-history')->assertRedirect('/login');
    }

    /**
     * Test empty state when DB has no measurements (R3, strict data reality: no fake data or zero lines).
     */
    public function test_charts_weight_empty_state_when_no_data(): void
    {
        $response = $this->actingAs($this->user)->getJson('/charts/weight');

        $response->assertOk()
            ->assertJson([
                'empty' => true,
                'metric' => 'weight_kg',
                'labels' => [],
                'values' => [],
                'moving_averages' => [],
            ]);
    }

    /**
     * User isolation: User A cannot see User B's body measurements in chart data.
     */
    public function test_charts_weight_user_isolation(): void
    {
        BodyMeasurement::create([
            'user_id' => $this->otherUser->id,
            'measured_at' => Carbon::now()->subDays(2)->toDateString(),
            'weight_kg' => 85.5,
        ]);

        $response = $this->actingAs($this->user)->getJson('/charts/weight');

        $response->assertOk()
            ->assertJson([
                'empty' => true,
                'values' => [],
            ]);
    }

    /**
     * Metric and range filtering for body measurements.
     */
    public function test_charts_weight_metric_and_range_filtering(): void
    {
        $now = Carbon::now('Asia/Jakarta');

        // Old measurement 6 weeks ago
        BodyMeasurement::create([
            'user_id' => $this->user->id,
            'measured_at' => $now->copy()->subWeeks(6)->toDateString(),
            'weight_kg' => 74.0,
            'waist_cm' => 88.0,
        ]);

        // Recent measurement 1 week ago
        BodyMeasurement::create([
            'user_id' => $this->user->id,
            'measured_at' => $now->copy()->subWeeks(1)->toDateString(),
            'weight_kg' => 72.5,
            'waist_cm' => 86.0,
        ]);

        // 4w range for weight_kg should only include the recent one
        $resp4w = $this->actingAs($this->user)->getJson('/charts/weight?metric=weight_kg&range=4w');
        $resp4w->assertOk()
            ->assertJson([
                'empty' => false,
                'metric' => 'weight_kg',
                'range' => '4w',
            ]);
        $data4w = $resp4w->json();
        $this->assertCount(1, $data4w['values']);
        $this->assertEquals(72.5, $data4w['values'][0]);

        // 8w range for waist_cm should include both
        $resp8w = $this->actingAs($this->user)->getJson('/charts/weight?metric=waist_cm&range=8w');
        $resp8w->assertOk()
            ->assertJson([
                'empty' => false,
                'metric' => 'waist_cm',
                'unit' => 'cm',
                'range' => '8w',
            ]);
        $data8w = $resp8w->json();
        $this->assertCount(2, $data8w['values']);
        $this->assertEquals(88.0, $data8w['values'][0]);
        $this->assertEquals(86.0, $data8w['values'][1]);
    }

    /**
     * Rolling 7-day average requires >= 4 data points in [date-6, date] (PLAN.md §8.8 & D13).
     */
    public function test_charts_weight_rolling_average_requires_four_points(): void
    {
        $baseDate = Carbon::now('Asia/Jakarta')->startOfWeek();

        // 3 data points in a 7-day window -> rolling average must be null
        for ($i = 0; $i < 3; $i++) {
            BodyMeasurement::create([
                'user_id' => $this->user->id,
                'measured_at' => $baseDate->copy()->addDays($i)->toDateString(),
                'weight_kg' => 70.0 + $i, // 70.0, 71.0, 72.0
            ]);
        }

        $res3 = $this->actingAs($this->user)->getJson('/charts/weight?range=4w');
        $res3->assertOk();
        $data3 = $res3->json();
        $this->assertCount(3, $data3['values']);
        $this->assertNull($data3['moving_averages'][0]);
        $this->assertNull($data3['moving_averages'][1]);
        $this->assertNull($data3['moving_averages'][2]);
        $this->assertFalse($data3['has_moving_average']);

        // Add 4th data point in the same 7-day window -> rolling average must be calculated
        BodyMeasurement::create([
            'user_id' => $this->user->id,
            'measured_at' => $baseDate->copy()->addDays(3)->toDateString(),
            'weight_kg' => 73.0,
        ]);

        $res4 = $this->actingAs($this->user)->getJson('/charts/weight?range=4w');
        $res4->assertOk();
        $data4 = $res4->json();
        $this->assertCount(4, $data4['values']);
        // 4th point has 4 points in window: (70 + 71 + 72 + 73) / 4 = 71.5
        $this->assertEquals(71.5, $data4['moving_averages'][3]);
        $this->assertTrue($data4['has_moving_average']);
    }

    /**
     * Weekly running chart returns empty state if no program exists.
     */
    public function test_charts_run_weekly_empty_state_when_no_program(): void
    {
        $response = $this->actingAs($this->user)->getJson('/charts/run-weekly');

        $response->assertOk()
            ->assertJson([
                'empty' => true,
                'weeks' => [],
            ]);
    }

    /**
     * Weekly running chart: 20-week target vs actual completed km.
     */
    public function test_charts_run_weekly_target_and_actual_km(): void
    {
        $startDate = '2026-11-02'; // Monday
        $generator = app(ProgramGenerator::class);
        $program = $generator->generate($this->user, $startDate);

        // Target for week 1 should be 6.5 km per PLAN.md §6.1
        $response = $this->actingAs($this->user)->getJson('/charts/run-weekly');
        $response->assertOk()
            ->assertJson([
                'empty' => false,
            ]);

        $data = $response->json();
        $this->assertCount(20, $data['weeks']);
        $this->assertEquals(6.5, $data['weeks'][0]['target_km']); // Week 1 (Tue 2.0 + Fri 2.0 + Sun 2.5 = 6.5)
        $this->assertEquals(0.0, $data['weeks'][0]['actual_km']);
        $this->assertFalse($data['has_runs']);

        // Log an actual run in Week 1 (e.g. 2026-11-03)
        Workout::create([
            'user_id' => $this->user->id,
            'workout_type' => 'run',
            'title' => 'Easy Run 5K',
            'performed_on' => '2026-11-03',
            'performed_at' => '2026-11-03 06:30:00',
            'distance_km' => 5.2,
            'duration_seconds' => 1800,
            'status' => 'completed',
        ]);

        $response2 = $this->actingAs($this->user)->getJson('/charts/run-weekly');
        $response2->assertOk();
        $data2 = $response2->json();
        $this->assertEquals(5.2, $data2['weeks'][0]['actual_km']);
        $this->assertTrue($data2['has_runs']);
    }

    /**
     * Exercise progress history returns empty state when user has no workouts logged.
     */
    public function test_charts_exercise_history_empty_state_when_no_workouts(): void
    {
        $response = $this->actingAs($this->user)->getJson('/charts/exercise-history');

        $response->assertOk()
            ->assertJson([
                'empty' => true,
                'available_exercises' => [],
                'sessions' => [],
            ]);
    }

    /**
     * Exercise progress history calculates highest weight and session volume (excluding warmup).
     */
    public function test_charts_exercise_history_max_weight_and_volume(): void
    {
        $exercise = Exercise::firstWhere('name', 'DB bench press');

        // Session 1: 2026-11-03
        $w1 = Workout::create([
            'user_id' => $this->user->id,
            'workout_type' => 'gym',
            'title' => 'Push A',
            'performed_on' => '2026-11-03',
            'performed_at' => '2026-11-03 07:00:00',
            'status' => 'completed',
        ]);

        $we1 = WorkoutExercise::create([
            'workout_id' => $w1->id,
            'exercise_id' => $exercise->id,
            'exercise_name' => $exercise->name,
            'order_in_workout' => 1,
        ]);

        // Warmup: 10kg x 10 reps (warmup volume excluded from working volume)
        ExerciseSet::create([
            'workout_exercise_id' => $we1->id,
            'set_number' => 1,
            'is_warmup' => true,
            'weight_kg' => 10.0,
            'reps' => 10,
        ]);

        // Working set 1: 20kg x 10 reps = 200kg
        ExerciseSet::create([
            'workout_exercise_id' => $we1->id,
            'set_number' => 2,
            'is_warmup' => false,
            'weight_kg' => 20.0,
            'reps' => 10,
        ]);

        // Working set 2: 24kg x 10 reps = 240kg (Max weight: 24kg, Total working vol: 440kg)
        ExerciseSet::create([
            'workout_exercise_id' => $we1->id,
            'set_number' => 3,
            'is_warmup' => false,
            'weight_kg' => 24.0,
            'reps' => 10,
        ]);

        $response = $this->actingAs($this->user)->getJson('/charts/exercise-history?exercise_id=' . $exercise->id);

        $response->assertOk()
            ->assertJson([
                'empty' => false,
                'exercise' => [
                    'id' => $exercise->id,
                    'name' => $exercise->name,
                ],
            ]);

        $data = $response->json();
        $this->assertCount(1, $data['sessions']);
        $this->assertEquals(24.0, $data['sessions'][0]['max_weight_kg']);
        $this->assertEquals(440.0, $data['sessions'][0]['volume_kg']);
        $this->assertEquals(3, $data['sessions'][0]['sets_count']);
    }

    /**
     * Today view renders desktop charts container, sr-only tables, and actual nutrition metrics.
     */
    public function test_today_desktop_renders_charts_and_nutrition_metrics(): void
    {
        $startDate = '2026-11-02';
        $generator = app(ProgramGenerator::class);
        $generator->generate($this->user, $startDate);

        $today = Carbon::now('Asia/Jakarta')->toDateString();

        $food = Food::create([
            'user_id' => $this->user->id,
            'name' => 'Nasi Putih',
            'serving_name' => '1 porsi',
            'serving_size_g' => 100,
            'calories_kcal' => 130,
            'protein_g' => 2.5,
            'carbs_g' => 28.0,
            'fat_g' => 0.3,
            'is_favorite' => false,
        ]);

        FoodLog::create([
            'user_id' => $this->user->id,
            'food_id' => $food->id,
            'food_name_snapshot' => $food->name,
            'serving_name_snapshot' => '1 porsi',
            'serving_size_g_snapshot' => 100,
            'logged_at' => Carbon::now('Asia/Jakarta'),
            'logged_on' => $today,
            'meal_type' => 'lunch',
            'servings' => 2,
            'calories_kcal' => 260,
            'protein_g' => 5.0,
            'carbs_g' => 56.0,
            'fat_g' => 0.6,
        ]);

        $response = $this->actingAs($this->user)->get('/');

        $response->assertOk()
            ->assertSee('runWeeklyChart')
            ->assertSee('miniWeightChart')
            ->assertSee('260') // actual calories
            ->assertSee('5 g') // actual protein
            ->assertSee('Tabel Data Jarak Lari Mingguan'); // accessible sr-only table
    }

    /**
     * Body index view renders chart canvases and sr-only data tables.
     */
    public function test_body_index_renders_chart_canvases_and_sr_only_tables(): void
    {
        $response = $this->actingAs($this->user)->get('/body');

        $response->assertOk()
            ->assertSee('bodyMetricChart')
            ->assertSee('exerciseHistoryChart')
            ->assertSee('Tabel Data Riwayat Metrik Tubuh')
            ->assertSee('Tabel Data Progres Latihan Beban');
    }
}
