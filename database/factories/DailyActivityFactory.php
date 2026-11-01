<?php

namespace Database\Factories;

use App\Models\DailyActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DailyActivityFactory extends Factory
{
    protected $model = DailyActivity::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'activity_date' => now()->toDateString(),
            'steps' => 8420,
            'source' => 'manual',
            'notes' => null,
        ];
    }
}
