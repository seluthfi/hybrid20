<?php

namespace Tests\Feature;

use App\Models\BodyMeasurement;
use App\Models\DailyActivity;
use App\Models\Program;
use App\Models\ProgramWeek;
use App\Models\ScheduledWorkout;
use App\Models\User;
use App\Models\Workout;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodayTest extends TestCase
{
    use RefreshDatabase;

    public function test_today_view_without_program_shows_empty_state(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('today'));
        $response->assertOk();
        $response->assertSee('Belum Ada Program Aktif');
    }

    public function test_today_view_before_start_date_shows_countdown(): void
    {
        $user = User::factory()->create();
        Program::factory()->create([
            'user_id' => $user->id,
            'start_date' => Carbon::now('Asia/Jakarta')->addDays(5)->startOfWeek()->toDateString(),
            'status' => 'planned',
        ]);

        $response = $this->actingAs($user)->get(route('today'));
        $response->assertOk();
        $response->assertSee('Program Dimulai');
    }

    public function test_today_view_shows_active_scheduled_workouts_and_metrics(): void
    {
        $user = User::factory()->create();
        $today = Carbon::now('Asia/Jakarta');
        // Start date is this week's Monday
        $monday = $today->copy()->startOfWeek(Carbon::MONDAY)->toDateString();

        $program = Program::factory()->create([
            'user_id' => $user->id,
            'start_date' => $monday,
            'status' => 'active',
        ]);

        $week = ProgramWeek::factory()->create([
            'program_id' => $program->id,
            'calendar_position' => 1,
            'week_number' => 1,
        ]);

        $todayWeekday = $today->dayOfWeekIso;

        $scheduled = ScheduledWorkout::factory()->create([
            'program_week_id' => $week->id,
            'weekday' => $todayWeekday,
            'workout_type' => 'gym',
            'title' => 'Latihan Hari Ini',
            'status' => 'planned',
            'sort_order' => 1,
        ]);

        DailyActivity::create([
            'user_id' => $user->id,
            'activity_date' => $today->toDateString(),
            'steps' => 6500,
        ]);

        BodyMeasurement::create([
            'user_id' => $user->id,
            'measured_at' => $today->toDateString(),
            'weight_kg' => 74.0,
        ]);

        $response = $this->actingAs($user)->get(route('today'));
        $response->assertOk();
        $response->assertSee('Latihan Hari Ini');
        $response->assertSee('6.500');
        $response->assertSee('74,0');
        $response->assertSee('Mulai Latihan');
    }

    public function test_pain_alert_appears_when_pain_logged_in_last_14_days(): void
    {
        $user = User::factory()->create();
        $today = Carbon::now('Asia/Jakarta');
        $monday = $today->copy()->startOfWeek(Carbon::MONDAY)->toDateString();

        $program = Program::factory()->create([
            'user_id' => $user->id,
            'start_date' => $monday,
            'status' => 'active',
        ]);

        ProgramWeek::factory()->create([
            'program_id' => $program->id,
            'calendar_position' => 1,
        ]);

        // Create 2 workouts with pain_flag within 14 days
        Workout::factory()->create([
            'user_id' => $user->id,
            'title' => 'Sesi Lari Nyeri',
            'pain_flag' => true,
            'performed_on' => $today->copy()->subDays(3)->toDateString(),
        ]);
        Workout::factory()->create([
            'user_id' => $user->id,
            'title' => 'Sesi Gym Nyeri',
            'pain_flag' => true,
            'performed_on' => $today->copy()->subDays(1)->toDateString(),
        ]);

        $response = $this->actingAs($user)->get(route('today'));
        $response->assertOk();
        // Rule §8.12
        $response->assertSee('Ada catatan nyeri yang berulang.');
        $response->assertSee('Pertimbangkan menghentikan progresi dan berkonsultasi dengan tenaga kesehatan.');
    }
}
