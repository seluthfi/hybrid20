<?php

namespace Database\Factories;

use App\Models\ProgramWeek;
use App\Models\ScheduledWorkout;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduledWorkoutFactory extends Factory
{
    protected $model = ScheduledWorkout::class;

    public function definition(): array
    {
        return [
            'program_week_id' => ProgramWeek::factory(),
            'weekday' => 1,
            'workout_type' => 'gym',
            'title' => 'Push',
            'planned_distance_km' => null,
            'planned_duration_minutes' => null,
            'intensity_label' => null,
            'sort_order' => 1,
            'instructions' => null,
            'status' => 'planned',
            'rescheduled_to' => null,
        ];
    }
}
