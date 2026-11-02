<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\Workout;
use App\Models\WorkoutExercise;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkoutExerciseFactory extends Factory
{
    protected $model = WorkoutExercise::class;

    public function definition(): array
    {
        return [
            'workout_id' => Workout::factory(),
            'exercise_id' => Exercise::factory(),
            'exercise_name' => 'DB bench press',
            'sort_order' => 1,
            'notes' => null,
        ];
    }
}
