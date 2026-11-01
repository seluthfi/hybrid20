<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\ProgramWeek;
use App\Models\ScheduledWorkout;
use App\Models\User;
use App\Models\Workout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkoutDetailAndDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_own_workout_detail(): void
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create([
            'user_id' => $user->id,
            'title' => 'Sesi Lari Pagi',
            'workout_type' => 'run',
            'distance_km' => 5.0,
            'duration_seconds' => 1800,
            'pace_seconds_per_km' => 360,
        ]);

        $response = $this->actingAs($user)->get(route('workouts.show', $workout->id));
        $response->assertOk();
        $response->assertSee('Sesi Lari Pagi');
        $response->assertSee("6'00\"/km");
    }

    public function test_user_cannot_view_or_delete_another_users_workout(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $workout = Workout::factory()->create([
            'user_id' => $user2->id,
            'title' => 'Latihan Rahasia',
        ]);

        $this->actingAs($user1)->get(route('workouts.show', $workout->id))->assertNotFound();
        $this->actingAs($user1)->delete(route('workouts.destroy', $workout->id))->assertNotFound();
    }

    public function test_deleting_workout_resets_scheduled_workout_status_to_planned_rule_d23(): void
    {
        $user = User::factory()->create();
        $program = Program::factory()->create(['user_id' => $user->id]);
        $week = ProgramWeek::factory()->create(['program_id' => $program->id, 'calendar_position' => 1]);
        $scheduled = ScheduledWorkout::factory()->create([
            'program_week_id' => $week->id,
            'workout_type' => 'gym',
            'status' => 'completed',
        ]);

        $workout = Workout::factory()->create([
            'user_id' => $user->id,
            'scheduled_workout_id' => $scheduled->id,
            'status' => 'completed',
        ]);

        $this->actingAs($user)->delete(route('workouts.destroy', $workout->id));

        $this->assertDatabaseMissing('workouts', ['id' => $workout->id]);
        $scheduled->refresh();
        $this->assertSame('planned', $scheduled->status);
    }
}
