<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\ScheduledWorkout;
use App\Models\ScheduledWorkoutExercise;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduledWorkoutExerciseFactory extends Factory
{
    protected $model = ScheduledWorkoutExercise::class;

    public function definition(): array
    {
        return [
            'scheduled_workout_id' => ScheduledWorkout::factory(),
            'exercise_id' => Exercise::factory(),
            'exercise_name' => 'DB bench press',
            'target_sets' => 3,
            'target_reps' => 10,
            'target_duration_seconds' => null,
            'sort_order' => 1,
            'notes' => null,
        ];
    }
}
