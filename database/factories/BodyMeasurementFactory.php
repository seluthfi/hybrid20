<?php

namespace Database\Factories;

use App\Models\BodyMeasurement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BodyMeasurementFactory extends Factory
{
    protected $model = BodyMeasurement::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'measured_at' => now()->toDateString(),
            'weight_kg' => 74.20,
            'waist_cm' => 82.0,
            'chest_cm' => 96.0,
            'arm_cm' => 34.0,
            'thigh_cm' => 56.0,
            'notes' => null,
        ];
    }
}
