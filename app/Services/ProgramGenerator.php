<?php

namespace App\Services;

use App\Models\Exercise;
use App\Models\Program;
use App\Models\ProgramWeek;
use App\Models\ScheduledWorkout;
use App\Models\ScheduledWorkoutExercise;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\GymTemplateSeeder;
use Database\Seeders\RunningPlanSeeder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ProgramGenerator
{
    /**
     * Generate full 20-week program for a user starting on a Monday.
     */
    public function generate(User $user, Carbon|string $startDate, int $durationWeeks = 20, string $name = 'HYBRID 20 Program'): Program
    {
        $start = Carbon::parse($startDate)->startOfDay();

        if (! $start->isMonday()) {
            throw new InvalidArgumentException('Tanggal mulai harus hari Senin.');
        }

        return DB::transaction(function () use ($user, $start, $durationWeeks, $name) {
            $program = Program::create([
                'user_id' => $user->id,
                'name' => $name,
                'start_date' => $start->toDateString(),
                'duration_weeks' => $durationWeeks,
                'status' => 'active',
                'notes' => null,
            ]);

            $runningData = RunningPlanSeeder::data();
            $gymTemplate = GymTemplateSeeder::template();
            $exerciseMap = Exercise::pluck('id', 'name')->toArray();

            for ($w = 1; $w <= $durationWeeks; $w++) {
                $runWeek = $runningData[$w] ?? [
                    'tue' => 2.0,
                    'fri' => 2.0,
                    'sun' => 2.5,
                    'phase_label' => null,
                ];

                $phaseLabel = $runWeek['phase_label'] ?? null;

                $week = ProgramWeek::create([
                    'program_id' => $program->id,
                    'week_number' => $w,
                    'calendar_position' => $w,
                    'is_repeat' => false,
                    'phase_label' => $phaseLabel,
                    'notes' => null,
                ]);

                // 1. Seed Gym Workouts for Weekdays 1..5 and Rest for Weekday 6
                foreach ($gymTemplate as $weekday => $dayData) {
                    $sw = ScheduledWorkout::create([
                        'program_week_id' => $week->id,
                        'weekday' => $weekday,
                        'workout_type' => $dayData['workout_type'],
                        'title' => $dayData['title'],
                        'planned_distance_km' => null,
                        'planned_duration_minutes' => null,
                        'intensity_label' => null,
                        'sort_order' => $dayData['sort_order'],
                        'instructions' => $dayData['instructions'],
                        'status' => 'planned',
                        'rescheduled_to' => null,
                    ]);

                    if (! empty($dayData['exercises'])) {
                        $sortEx = 1;
                        foreach ($dayData['exercises'] as $ex) {
                            ScheduledWorkoutExercise::create([
                                'scheduled_workout_id' => $sw->id,
                                'exercise_id' => $exerciseMap[$ex['name']] ?? null,
                                'exercise_name' => $ex['name'],
                                'target_sets' => $ex['target_sets'],
                                'target_reps' => $ex['target_reps'],
                                'target_duration_seconds' => $ex['target_duration_seconds'],
                                'sort_order' => $sortEx++,
                                'notes' => null,
                            ]);
                        }
                    }
                }

                // 2. Seed Running Workouts for Weekdays 2 (Tue), 5 (Fri), 7 (Sun)
                // Tuesday (weekday 2): sort_order 2
                ScheduledWorkout::create([
                    'program_week_id' => $week->id,
                    'weekday' => 2,
                    'workout_type' => 'run',
                    'title' => 'Easy run',
                    'planned_distance_km' => $runWeek['tue'],
                    'planned_duration_minutes' => null,
                    'intensity_label' => 'easy',
                    'sort_order' => 2,
                    'instructions' => 'Pace mudah, masih bisa berbicara. Jarak adalah target fleksibel. Beri jeda beberapa jam antara gym dan lari.',
                    'status' => 'planned',
                    'rescheduled_to' => null,
                ]);

                // Friday (weekday 5): sort_order 2
                ScheduledWorkout::create([
                    'program_week_id' => $week->id,
                    'weekday' => 5,
                    'workout_type' => 'run',
                    'title' => 'Easy run',
                    'planned_distance_km' => $runWeek['fri'],
                    'planned_duration_minutes' => null,
                    'intensity_label' => 'easy',
                    'sort_order' => 2,
                    'instructions' => 'Easy run atau sesi ringan sesuai kesiapan. Beri jeda beberapa jam antara gym dan lari.',
                    'status' => 'planned',
                    'rescheduled_to' => null,
                ]);

                // Sunday (weekday 7): sort_order 1
                if ($w === 20) {
                    ScheduledWorkout::create([
                        'program_week_id' => $week->id,
                        'weekday' => 7,
                        'workout_type' => 'run',
                        'title' => 'Percobaan 10K',
                        'planned_distance_km' => 10.0,
                        'planned_duration_minutes' => null,
                        'intensity_label' => 'taper_or_test',
                        'sort_order' => 1,
                        'instructions' => 'Percobaan 10K hanya bila progres sebelumnya dapat ditoleransi, tubuh pulih, dan tidak ada nyeri yang mengubah cara berlari. Pace mudah; ini bukan tes kecepatan.',
                        'status' => 'planned',
                        'rescheduled_to' => null,
                    ]);
                } else {
                    ScheduledWorkout::create([
                        'program_week_id' => $week->id,
                        'weekday' => 7,
                        'workout_type' => 'run',
                        'title' => 'Long run',
                        'planned_distance_km' => $runWeek['sun'],
                        'planned_duration_minutes' => null,
                        'intensity_label' => 'long_run',
                        'sort_order' => 1,
                        'instructions' => 'Long run dengan pace mudah. Boleh dikurangi atau dibagi bila belum pulih.',
                        'status' => 'planned',
                        'rescheduled_to' => null,
                    ]);
                }
            }

            return $program;
        });
    }
}
