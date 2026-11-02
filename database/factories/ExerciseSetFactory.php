<?php

namespace Database\Factories;

use App\Models\ExerciseSet;
use App\Models\WorkoutExercise;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExerciseSetFactory extends Factory
{
    protected $model = ExerciseSet::class;

    public function definition(): array
    {
        return [
            'workout_exercise_id' => WorkoutExercise::factory(),
            'set_number' => 1,
            'reps' => 10,
            'weight_kg' => 15.00,
            'duration_seconds' => null,
            'distance_m' => null,
            'rpe' => 7,
            'is_warmup' => false,
        ];
    }
}
