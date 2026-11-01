<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\User;
use App\Services\ProgramGenerator;
use Database\Seeders\DefaultExerciseSeeder;
use Database\Seeders\RunningPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class ProgramDatabaseTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Program $program;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultExerciseSeeder::class);

        $this->user = User::factory()->create();
        $generator = new ProgramGenerator();
        // 2026-11-02 adalah hari Senin
        $this->program = $generator->generate($this->user, '2026-11-02', 20);
    }

    /**
     * Uji total km per minggu persis sesuai tabel PLAN.md §6.1.
     */
    public function test_total_running_distance_per_week_matches_section_6_1_exactly(): void
    {
        $expectedData = RunningPlanSeeder::data();

        $this->assertCount(20, $this->program->weeks);

        foreach ($this->program->weeks as $week) {
            $w = $week->week_number;
            $expected = $expectedData[$w];

            // 3 Sesi Lari: Selasa (2), Jumat (5), Minggu (7)
            $tueRun = $week->scheduledWorkouts()->where('weekday', 2)->where('workout_type', 'run')->first();
            $friRun = $week->scheduledWorkouts()->where('weekday', 5)->where('workout_type', 'run')->first();
            $sunRun = $week->scheduledWorkouts()->where('weekday', 7)->where('workout_type', 'run')->first();

            $this->assertNotNull($tueRun, "Sesi lari Selasa minggu {$w} harus ada");
            $this->assertNotNull($friRun, "Sesi lari Jumat minggu {$w} harus ada");
            $this->assertNotNull($sunRun, "Sesi lari Minggu minggu {$w} harus ada");

            $this->assertEquals((float)$expected['tue'], (float)$tueRun->planned_distance_km, "Jarak lari Selasa M{$w} tidak cocok");
            $this->assertEquals((float)$expected['fri'], (float)$friRun->planned_distance_km, "Jarak lari Jumat M{$w} tidak cocok");
            $this->assertEquals((float)$expected['sun'], (float)$sunRun->planned_distance_km, "Jarak lari Minggu M{$w} tidak cocok");

            $weekTotal = (float)$tueRun->planned_distance_km + (float)$friRun->planned_distance_km + (float)$sunRun->planned_distance_km;
            $this->assertEquals((float)$expected['total'], $weekTotal, "Total km minggu {$w} tidak cocok dengan tabel §6.1");
        }
    }

    /**
     * Uji jumlah set per hari gym: 15 (Senin), 13 (Selasa), 15 (Rabu), 14 (Kamis), 12 (Jumat). Total = 69 set.
     */
    public function test_gym_sets_per_day_match_section_6_2(): void
    {
        $firstWeek = $this->program->weeks()->where('week_number', 1)->first();

        $expectedSetsPerDay = [
            1 => 15, // Senin: Push
            2 => 13, // Selasa: Back / Biceps / Traps
            3 => 15, // Rabu: Legs / Abs
            4 => 14, // Kamis: Upper
            5 => 12, // Jumat: Back / Biceps / Traps (ringan)
        ];

        $totalSetsWeek = 0;

        foreach ($expectedSetsPerDay as $weekday => $expectedSets) {
            $gymWorkout = $firstWeek->scheduledWorkouts()
                ->where('weekday', $weekday)
                ->where('workout_type', 'gym')
                ->first();

            $this->assertNotNull($gymWorkout, "Sesi gym weekday {$weekday} harus ada");

            $daySets = $gymWorkout->exercises()->sum('target_sets');
            $this->assertEquals($expectedSets, $daySets, "Jumlah set gym hari {$weekday} harus {$expectedSets}");
            $totalSetsWeek += $daySets;
        }

        $this->assertEquals(69, $totalSetsWeek, 'Total set gym mingguan harus tepat 69 set');
    }

    /**
     * Uji 8 sesi terjadwal aktif per minggu (5 gym + 3 lari). Sesi istirahat Sabtu bertipe rest.
     */
    public function test_eight_scheduled_sessions_per_week(): void
    {
        foreach ($this->program->weeks as $week) {
            $activeSessions = $week->scheduledWorkouts()
                ->whereIn('workout_type', ['gym', 'run'])
                ->count();

            $this->assertEquals(8, $activeSessions, "Minggu {$week->week_number} harus memiliki 8 sesi terjadwal (5 gym + 3 lari)");

            // Verifikasi hari Sabtu adalah istirahat penuh (rest)
            $satRest = $week->scheduledWorkouts()->where('weekday', 6)->first();
            $this->assertNotNull($satRest);
            $this->assertEquals('rest', $satRest->workout_type);
            $this->assertEquals('Istirahat penuh', $satRest->title);
        }
    }

    /**
     * Uji phase_label (deload di minggu 4, 8, 12, 16, 19; taper di minggu 20; null lainnya).
     */
    public function test_phase_labels_are_accurate(): void
    {
        $deloadWeeks = [4, 8, 12, 16, 19];

        foreach ($this->program->weeks as $week) {
            if (in_array($week->week_number, $deloadWeeks)) {
                $this->assertEquals('deload', $week->phase_label, "Minggu {$week->week_number} harus berlabel deload");
            } elseif ($week->week_number === 20) {
                $this->assertEquals('taper', $week->phase_label, "Minggu 20 harus berlabel taper");
            } else {
                $this->assertNull($week->phase_label, "Minggu {$week->week_number} seharusnya tidak memiliki phase_label");
            }
        }
    }

    /**
     * Uji intensity_label sesi lari (Selasa & Jumat = easy, Minggu = long_run, Minggu 20 Minggu = taper_or_test).
     */
    public function test_session_intensity_labels(): void
    {
        foreach ($this->program->weeks as $week) {
            $tue = $week->scheduledWorkouts()->where('weekday', 2)->where('workout_type', 'run')->first();
            $fri = $week->scheduledWorkouts()->where('weekday', 5)->where('workout_type', 'run')->first();
            $sun = $week->scheduledWorkouts()->where('weekday', 7)->where('workout_type', 'run')->first();

            $this->assertEquals('easy', $tue->intensity_label);
            $this->assertEquals('easy', $fri->intensity_label);

            if ($week->week_number === 20) {
                $this->assertEquals('taper_or_test', $sun->intensity_label);
                $this->assertEquals('Percobaan 10K', $sun->title);
            } else {
                $this->assertEquals('long_run', $sun->intensity_label);
                $this->assertEquals('Long run', $sun->title);
            }
        }
    }

    /**
     * Uji instruksi baku Minggu 20 hari Minggu (Percobaan 10K).
     */
    public function test_week_20_instructions_match_spec_exactly(): void
    {
        $week20 = $this->program->weeks()->where('week_number', 20)->first();
        $trialRun = $week20->scheduledWorkouts()->where('weekday', 7)->first();

        $expectedInstruction = 'Percobaan 10K hanya bila progres sebelumnya dapat ditoleransi, tubuh pulih, dan tidak ada nyeri yang mengubah cara berlari. Pace mudah; ini bukan tes kecepatan.';

        $this->assertEquals($expectedInstruction, $trialRun->instructions);
    }

    /**
     * Uji validasi hari Senin pada ProgramGenerator (D5).
     */
    public function test_program_generator_requires_monday_start_date(): void
    {
        $generator = new ProgramGenerator();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tanggal mulai harus hari Senin.');

        // 2026-11-03 adalah hari Selasa
        $generator->generate($this->user, '2026-11-03');
    }
}
