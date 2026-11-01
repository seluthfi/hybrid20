<?php

namespace Database\Seeders;

use App\Models\Exercise;
use Illuminate\Database\Seeder;

class DefaultExerciseSeeder extends Seeder
{
    /**
     * Seed the default exercise catalog.
     */
    public function run(): void
    {
        $exercises = [
            // Push
            ['name' => 'DB bench press', 'muscle_group' => 'Push', 'equipment' => 'Dumbbell'],
            ['name' => 'DB fly', 'muscle_group' => 'Push', 'equipment' => 'Dumbbell'],
            ['name' => 'Overhead triceps extension', 'muscle_group' => 'Triceps', 'equipment' => 'Dumbbell'],
            ['name' => 'Triceps kickback', 'muscle_group' => 'Triceps', 'equipment' => 'Dumbbell'],
            ['name' => 'Shoulder press', 'muscle_group' => 'Shoulder', 'equipment' => 'Dumbbell'],
            ['name' => 'Lateral raise', 'muscle_group' => 'Shoulder', 'equipment' => 'Dumbbell'],
            ['name' => 'Incline bench press', 'muscle_group' => 'Push', 'equipment' => 'Dumbbell / Barbell'],
            ['name' => 'Triceps extension', 'muscle_group' => 'Triceps', 'equipment' => 'Cable / Dumbbell'],

            // Back / Biceps / Traps
            ['name' => 'Bent-over DB row', 'muscle_group' => 'Back', 'equipment' => 'Dumbbell'],
            ['name' => 'Rear delt fly', 'muscle_group' => 'Rear delt', 'equipment' => 'Dumbbell'],
            ['name' => 'Biceps curl', 'muscle_group' => 'Biceps', 'equipment' => 'Dumbbell'],
            ['name' => 'Hammer curl', 'muscle_group' => 'Biceps', 'equipment' => 'Dumbbell'],
            ['name' => 'Shrug', 'muscle_group' => 'Traps', 'equipment' => 'Dumbbell'],
            ['name' => 'Dead hang', 'muscle_group' => 'Grip', 'equipment' => 'Pull-up Bar'],
            ['name' => 'Lat pulldown', 'muscle_group' => 'Back', 'equipment' => 'Cable Machine'],
            ['name' => 'Seated cable row', 'muscle_group' => 'Back', 'equipment' => 'Cable Machine'],
            ['name' => 'DB row', 'muscle_group' => 'Back', 'equipment' => 'Dumbbell'],

            // Legs / Abs
            ['name' => 'Goblet squat', 'muscle_group' => 'Legs', 'equipment' => 'Dumbbell'],
            ['name' => 'Romanian deadlift', 'muscle_group' => 'Legs', 'equipment' => 'Dumbbell'],
            ['name' => 'Glute bridge', 'muscle_group' => 'Glutes', 'equipment' => 'Mat / Bodyweight'],
            ['name' => 'Calf raise', 'muscle_group' => 'Calves', 'equipment' => 'Bodyweight / Dumbbell'],
            ['name' => 'Leg raise', 'muscle_group' => 'Abs', 'equipment' => 'Mat / Bodyweight'],
            ['name' => 'Plank', 'muscle_group' => 'Abs', 'equipment' => 'Mat / Bodyweight'],

            // Mobility
            ['name' => 'Reverse snow angel', 'muscle_group' => 'Mobility', 'equipment' => 'Mat / Bodyweight'],
            ['name' => 'Wall angel', 'muscle_group' => 'Mobility', 'equipment' => 'Wall / Bodyweight'],
        ];

        foreach ($exercises as $exercise) {
            Exercise::firstOrCreate(
                ['name' => $exercise['name'], 'user_id' => null],
                [
                    'muscle_group' => $exercise['muscle_group'],
                    'equipment' => $exercise['equipment'],
                    'is_custom' => false,
                ]
            );
        }
    }
}
