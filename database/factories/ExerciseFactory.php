<?php

namespace Database\Factories;

use App\Models\Exercise;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExerciseFactory extends Factory
{
    protected $model = Exercise::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'name' => 'DB bench press',
            'muscle_group' => 'Push',
            'equipment' => 'Dumbbell',
            'is_custom' => false,
        ];
    }
}
