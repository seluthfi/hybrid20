<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\Program;
use App\Models\ProgramWeek;
use App\Models\ScheduledWorkout;
use App\Models\ScheduledWorkoutExercise;
use App\Models\User;
use App\Models\Workout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkoutGymTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_gym_workout_and_only_checked_sets_are_saved(): void
    {
        $user = User::factory()->create();
        $program = Program::factory()->create(['user_id' => $user->id]);
        $week = ProgramWeek::factory()->create(['program_id' => $program->id, 'calendar_position' => 1]);
        $scheduled = ScheduledWorkout::factory()->create([
            'program_week_id' => $week->id,
            'workout_type' => 'gym',
            'title' => 'Push Day',
            'status' => 'planned',
        ]);

        $ex1 = Exercise::factory()->create(['name' => 'Bench Press']);
        $ex2 = Exercise::factory()->create(['name' => 'Shoulder Press']);

        $response = $this->actingAs($user)->post(route('workouts.store'), [
            'workout_type' => 'gym',
            'scheduled_workout_id' => $scheduled->id,
            'title' => 'Push Day',
            'performed_at' => '2026-11-03T18:00',
            'duration' => '45:00',
            'rpe' => 8,
            'status' => 'completed',
            'body_condition' => 'segar',
            'exercises' => [
                // Exercise 1: 2 sets checked, 1 set unchecked
                [
                    'exercise_id' => $ex1->id,
                    'exercise_name' => 'Bench Press',
                    'sets' => [
                        ['set_number' => 1, 'weight_kg' => '60', 'reps' => '10', 'rpe' => '8', 'checked' => '1'],
                        ['set_number' => 2, 'weight_kg' => '60', 'reps' => '8', 'rpe' => '9', 'checked' => '1'],
                        ['set_number' => 3, 'weight_kg' => '60', 'reps' => '8', 'rpe' => '', 'checked' => ''], // UNCHECKED
                    ],
                ],
                // Exercise 2: all sets unchecked -> must NOT be saved into workout_exercises (D24)
                [
                    'exercise_id' => $ex2->id,
                    'exercise_name' => 'Shoulder Press',
                    'sets' => [
                        ['set_number' => 1, 'weight_kg' => '20', 'reps' => '10', 'checked' => ''],
                    ],
                ],
            ],
        ]);

        $response->assertRedirect();
        
        $workout = Workout::where('user_id', $user->id)->first();
        $this->assertNotNull($workout);
        $this->assertSame('gym', $workout->workout_type);
        $this->assertSame(8, $workout->rpe);
        $this->assertSame(2700, $workout->duration_seconds); // 45 * 60

        // Exercise 1 is saved, Exercise 2 is skipped (D24)
        $this->assertCount(1, $workout->exercises);
        $savedEx1 = $workout->exercises->first();
        $this->assertSame('Bench Press', $savedEx1->exercise_name);

        // Only 2 checked sets are saved
        $this->assertCount(2, $savedEx1->sets);

        // Volume check: 60*10 + 60*8 = 600 + 480 = 1080 kg
        $this->assertEquals(1080.0, $workout->calculateVolume());

        // Scheduled workout status is updated to completed
        $scheduled->refresh();
        $this->assertSame('completed', $scheduled->status);
    }

    public function test_rpe_must_be_integer_between_1_and_10(): void
    {
        $user = User::factory()->create();

        // RPE > 10 is rejected
        $response = $this->actingAs($user)->post(route('workouts.store'), [
            'workout_type' => 'gym',
            'title' => 'Latihan Gym',
            'rpe' => 12,
        ]);

        $response->assertSessionHasErrors('rpe');
    }
}
