<?php

namespace Database\Factories;

use App\Models\Food;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FoodFactory extends Factory
{
    protected $model = Food::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => 'Dada Ayam Panggang',
            'serving_name' => '1 potong',
            'serving_size_g' => 100.00,
            'calories_kcal' => 165.00,
            'protein_g' => 31.00,
            'carbs_g' => 0.00,
            'fat_g' => 3.60,
            'is_favorite' => false,
            'notes' => null,
        ];
    }
}
