<?php

namespace Database\Factories;

use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProgramFactory extends Factory
{
    protected $model = Program::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => 'HYBRID 20 Program',
            'start_date' => '2026-11-02', // Senin
            'duration_weeks' => 20,
            'status' => 'active',
            'notes' => null,
        ];
    }
}
