<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserSettingFactory extends Factory
{
    protected $model = UserSetting::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'height_cm' => 163.0,
            'starting_weight_kg' => 74.00,
            'target_weight_kg' => 60.00,
            'daily_calorie_target' => 2200,
            'protein_target_g' => 140,
            'carbs_target_g' => 260,
            'fat_target_g' => 65,
            'daily_steps_target' => 10000,
            'timezone' => 'Asia/Jakarta',
            'weight_unit' => 'kg',
        ];
    }
}
