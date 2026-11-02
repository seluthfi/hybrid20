<?php

namespace Tests\Feature;

use App\Models\DailyActivity;
use App\Models\Program;
use App\Models\ProgramWeek;
use App\Models\ScheduledWorkout;
use App\Models\User;
use App\Models\Workout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkoutRunTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_run_workout_with_pace_and_independent_steps(): void
    {
        $user = User::factory()->create();
        $program = Program::factory()->create(['user_id' => $user->id]);
        $week = ProgramWeek::factory()->create(['program_id' => $program->id, 'calendar_position' => 1]);
        $scheduled = ScheduledWorkout::factory()->create([
            'program_week_id' => $week->id,
            'workout_type' => 'run',
            'title' => 'Easy Run',
            'status' => 'planned',
        ]);

        // Pre-existing daily activity for today
        DailyActivity::create([
            'user_id' => $user->id,
            'activity_date' => '2026-11-03',
            'steps' => 8000,
        ]);

        $response = $this->actingAs($user)->post(route('workouts.store'), [
            'workout_type' => 'run',
            'scheduled_workout_id' => $scheduled->id,
            'title' => 'Easy Run',
            'performed_at' => '2026-11-03T06:30',
            'distance_km' => '2,1',
            'duration' => '12:11',
            'steps' => '2.420',
            'rpe' => 5,
            'status' => 'completed',
            'body_condition' => 'normal',
            'pain_flag' => '1',
            'pain_notes' => 'Kaki kanan sedikit kaku',
        ]);

        $response->assertRedirect();

        $workout = Workout::where('user_id', $user->id)->first();
        $this->assertNotNull($workout);
        $this->assertSame('run', $workout->workout_type);
        $this->assertEquals(2.1, (float) $workout->distance_km);
        $this->assertSame(731, $workout->duration_seconds); // 12 * 60 + 11
        // 731 / 2.1 = 348.095 -> 348
        $this->assertSame(348, $workout->pace_seconds_per_km);
        $this->assertSame(2420, $workout->steps);
        $this->assertTrue($workout->pain_flag);
        $this->assertSame('Kaki kanan sedikit kaku', $workout->pain_notes);

        // Crucial Rule R5: workouts.steps MUST NOT be added to daily_activities.steps
        $activity = DailyActivity::where('user_id', $user->id)->whereDate('activity_date', '2026-11-03')->first();
        $this->assertSame(8000, $activity->steps);

        // Scheduled workout status is completed
        $scheduled->refresh();
        $this->assertSame('completed', $scheduled->status);
    }

    public function test_zero_or_empty_distance_gives_null_pace_without_error(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('workouts.store'), [
            'workout_type' => 'run',
            'title' => 'Lari Ringan',
            'performed_at' => '2026-11-03T07:00',
            'distance_km' => '0',
            'duration' => '10:00',
        ]);

        $response->assertRedirect();
        $workout = Workout::where('user_id', $user->id)->first();
        $this->assertNull($workout->pace_seconds_per_km);
    }
}
