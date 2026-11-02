<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\ProgramWeek;
use App\Models\ScheduledWorkout;
use App\Models\User;
use App\Models\Workout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkoutHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_workout_history_and_filter_by_type(): void
    {
        $user = User::factory()->create();

        $gymWorkout = Workout::factory()->create([
            'user_id' => $user->id,
            'workout_type' => 'gym',
            'title' => 'Sesi Push',
            'performed_on' => '2026-11-02',
        ]);

        $runWorkout = Workout::factory()->create([
            'user_id' => $user->id,
            'workout_type' => 'run',
            'title' => 'Sesi Lari 5K',
            'performed_on' => '2026-11-03',
        ]);

        // Default: all
        $responseAll = $this->actingAs($user)->get(route('workouts.index'));
        $responseAll->assertOk();
        $responseAll->assertSee('Sesi Push');
        $responseAll->assertSee('Sesi Lari 5K');

        // Filter: gym
        $responseGym = $this->actingAs($user)->get(route('workouts.index', ['type' => 'gym']));
        $responseGym->assertOk();
        $responseGym->assertSee('Sesi Push');
        $responseGym->assertDontSee('Sesi Lari 5K');

        // Filter: run
        $responseRun = $this->actingAs($user)->get(route('workouts.index', ['type' => 'run']));
        $responseRun->assertOk();
        $responseRun->assertSee('Sesi Lari 5K');
        $responseRun->assertDontSee('Sesi Push');
    }

    public function test_user_only_sees_own_workouts_in_history(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Workout::factory()->create([
            'user_id' => $user2->id,
            'title' => 'Rahasia Pengguna Lain',
        ]);

        $response = $this->actingAs($user1)->get(route('workouts.index'));
        $response->assertOk();
        $response->assertDontSee('Rahasia Pengguna Lain');
        $response->assertSee('Belum Ada Catatan Latihan');
    }

    public function test_user_with_active_program_can_view_workout_history_with_stats(): void
    {
        $user = User::factory()->create();

        $program = Program::factory()->create([
            'user_id' => $user->id,
            'status' => 'active',
            'start_date' => now()->startOfWeek()->toDateString(),
            'duration_weeks' => 20,
        ]);

        $week = ProgramWeek::factory()->create([
            'program_id' => $program->id,
            'week_number' => 1,
            'calendar_position' => 1,
        ]);

        ScheduledWorkout::factory()->create([
            'program_week_id' => $week->id,
            'workout_type' => 'gym',
            'status' => 'completed',
        ]);

        ScheduledWorkout::factory()->create([
            'program_week_id' => $week->id,
            'workout_type' => 'run',
            'status' => 'planned',
        ]);

        $response = $this->actingAs($user)->get(route('workouts.index'));
        $response->assertOk();
        $response->assertSee('Minggu 1');
    }
}
