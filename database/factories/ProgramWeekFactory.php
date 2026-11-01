<?php

namespace Database\Factories;

use App\Models\Program;
use App\Models\ProgramWeek;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProgramWeekFactory extends Factory
{
    protected $model = ProgramWeek::class;

    public function definition(): array
    {
        return [
            'program_id' => Program::factory(),
            'week_number' => 1,
            'calendar_position' => 1,
            'is_repeat' => false,
            'phase_label' => null,
            'notes' => null,
        ];
    }
}
