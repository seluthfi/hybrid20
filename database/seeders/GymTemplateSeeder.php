<?php

namespace Database\Seeders;

class GymTemplateSeeder
{
    /**
     * Template mingguan sesi gym dan latihan persis sesuai PLAN.md §6.2.
     * Total 5 sesi gym + 1 rest = 6 hari template (Minggu diisi lari).
     * Total set gym = 15 + 13 + 15 + 14 + 12 = 69 set.
     */
    public static function template(): array
    {
        return [
            // Senin (1): Push (15 set)
            1 => [
                'title' => 'Push',
                'workout_type' => 'gym',
                'sort_order' => 1,
                'instructions' => null,
                'exercises' => [
                    ['name' => 'DB bench press', 'target_sets' => 3, 'target_reps' => 10, 'target_duration_seconds' => null],
                    ['name' => 'DB fly', 'target_sets' => 2, 'target_reps' => 12, 'target_duration_seconds' => null],
                    ['name' => 'Overhead triceps extension', 'target_sets' => 2, 'target_reps' => 12, 'target_duration_seconds' => null],
                    ['name' => 'Triceps kickback', 'target_sets' => 2, 'target_reps' => 12, 'target_duration_seconds' => null],
                    ['name' => 'Shoulder press', 'target_sets' => 3, 'target_reps' => 10, 'target_duration_seconds' => null],
                    ['name' => 'Lateral raise', 'target_sets' => 3, 'target_reps' => 12, 'target_duration_seconds' => null],
                ],
            ],

            // Selasa (2): Back / Biceps / Traps (13 set)
            2 => [
                'title' => 'Back / Biceps / Traps',
                'workout_type' => 'gym',
                'sort_order' => 1,
                'instructions' => 'Beri jeda beberapa jam antara gym dan lari.',
                'exercises' => [
                    ['name' => 'Bent-over DB row', 'target_sets' => 3, 'target_reps' => 12, 'target_duration_seconds' => null],
                    ['name' => 'Rear delt fly', 'target_sets' => 2, 'target_reps' => 12, 'target_duration_seconds' => null],
                    ['name' => 'Biceps curl', 'target_sets' => 2, 'target_reps' => 12, 'target_duration_seconds' => null],
                    ['name' => 'Hammer curl', 'target_sets' => 2, 'target_reps' => 12, 'target_duration_seconds' => null],
                    ['name' => 'Shrug', 'target_sets' => 2, 'target_reps' => 15, 'target_duration_seconds' => null],
                    ['name' => 'Dead hang', 'target_sets' => 2, 'target_reps' => null, 'target_duration_seconds' => 30],
                ],
            ],

            // Rabu (3): Legs / Abs (15 set)
            3 => [
                'title' => 'Legs / Abs',
                'workout_type' => 'gym',
                'sort_order' => 1,
                'instructions' => null,
                'exercises' => [
                    ['name' => 'Goblet squat', 'target_sets' => 2, 'target_reps' => 10, 'target_duration_seconds' => null],
                    ['name' => 'Romanian deadlift', 'target_sets' => 3, 'target_reps' => 10, 'target_duration_seconds' => null],
                    ['name' => 'Glute bridge', 'target_sets' => 2, 'target_reps' => 12, 'target_duration_seconds' => null],
                    ['name' => 'Calf raise', 'target_sets' => 3, 'target_reps' => 15, 'target_duration_seconds' => null],
                    ['name' => 'Leg raise', 'target_sets' => 2, 'target_reps' => 12, 'target_duration_seconds' => null],
                    ['name' => 'Plank', 'target_sets' => 3, 'target_reps' => null, 'target_duration_seconds' => 30],
                ],
            ],

            // Kamis (4): Upper (14 set)
            4 => [
                'title' => 'Upper',
                'workout_type' => 'gym',
                'sort_order' => 1,
                'instructions' => null,
                'exercises' => [
                    ['name' => 'Incline bench press', 'target_sets' => 3, 'target_reps' => 10, 'target_duration_seconds' => null],
                    ['name' => 'Lat pulldown', 'target_sets' => 3, 'target_reps' => 12, 'target_duration_seconds' => null],
                    ['name' => 'Seated cable row', 'target_sets' => 2, 'target_reps' => 12, 'target_duration_seconds' => null],
                    ['name' => 'Lateral raise', 'target_sets' => 2, 'target_reps' => 12, 'target_duration_seconds' => null],
                    ['name' => 'Triceps extension', 'target_sets' => 2, 'target_reps' => 12, 'target_duration_seconds' => null],
                    ['name' => 'Biceps curl', 'target_sets' => 2, 'target_reps' => 12, 'target_duration_seconds' => null],
                ],
            ],

            // Jumat (5): Back / Biceps / Traps (ringan) (12 set)
            5 => [
                'title' => 'Back / Biceps / Traps (ringan)',
                'workout_type' => 'gym',
                'sort_order' => 1,
                'instructions' => 'Sesi gym ringan. Beri jeda beberapa jam antara gym dan lari.',
                'exercises' => [
                    ['name' => 'DB row', 'target_sets' => 2, 'target_reps' => 12, 'target_duration_seconds' => null],
                    ['name' => 'Reverse snow angel', 'target_sets' => 2, 'target_reps' => 15, 'target_duration_seconds' => null],
                    ['name' => 'Biceps curl', 'target_sets' => 2, 'target_reps' => 12, 'target_duration_seconds' => null],
                    ['name' => 'Hammer curl', 'target_sets' => 2, 'target_reps' => 12, 'target_duration_seconds' => null],
                    ['name' => 'Wall angel', 'target_sets' => 2, 'target_reps' => 12, 'target_duration_seconds' => null],
                    ['name' => 'Shrug', 'target_sets' => 2, 'target_reps' => 15, 'target_duration_seconds' => null],
                ],
            ],

            // Sabtu (6): Istirahat penuh (0 set)
            6 => [
                'title' => 'Istirahat penuh',
                'workout_type' => 'rest',
                'sort_order' => 1,
                'instructions' => null,
                'exercises' => [],
            ],
        ];
    }
}
