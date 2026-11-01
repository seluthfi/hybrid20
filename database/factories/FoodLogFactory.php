<?php

namespace Database\Factories;

use App\Models\Food;
use App\Models\FoodLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FoodLogFactory extends Factory
{
    protected $model = FoodLog::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'food_id' => Food::factory(),
            'logged_at' => now(),
            'logged_on' => now()->toDateString(),
            'meal_type' => 'lunch',
            'food_name_snapshot' => 'Dada Ayam Panggang',
            'serving_name_snapshot' => '1 potong',
            'serving_size_g_snapshot' => 100.00,
            'servings' => 1.50,
            'calories_kcal' => 247.50,
            'protein_g' => 46.50,
            'carbs_g' => 0.00,
            'fat_g' => 5.40,
            'notes' => null,
        ];
    }
}
