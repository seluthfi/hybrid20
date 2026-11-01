<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Workout;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkoutFactory extends Factory
{
    protected $model = Workout::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'scheduled_workout_id' => null,
            'workout_type' => 'run',
            'title' => 'Easy run',
            'performed_at' => now(),
            'performed_on' => now()->toDateString(),
            'duration_seconds' => 731, // 12m 11s
            'distance_km' => 2.10,
            'pace_seconds_per_km' => 348,
            'steps' => 2420,
            'rpe' => 5,
            'status' => 'completed',
            'body_condition' => 'normal',
            'pain_flag' => false,
            'pain_notes' => null,
            'general_notes' => null,
        ];
    }
}
