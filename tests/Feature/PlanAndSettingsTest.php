<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\ProgramWeek;
use App\Models\ScheduledWorkout;
use App\Models\User;
use App\Models\UserSetting;
use App\Services\ProgramDateService;
use App\Services\ProgramGenerator;
use Carbon\Carbon;
use Database\Seeders\DefaultExerciseSeeder;
use Database\Seeders\GymTemplateSeeder;
use Database\Seeders\RunningPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanAndSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected ProgramDateService $dateService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultExerciseSeeder::class);

        $this->user = User::factory()->create([
            'email' => 'athlete@hybrid20.local',
        ]);

        UserSetting::create([
            'user_id' => $this->user->id,
            'height_cm' => 163,
            'starting_weight_kg' => 74.0,
            'target_weight_kg' => 60.0,
            'daily_calorie_target' => 2200,
            'protein_target_g' => 140,
            'carbs_target_g' => 260,
            'fat_target_g' => 65,
            'daily_steps_target' => 10000,
            'timezone' => 'Asia/Jakarta',
            'weight_unit' => 'kg',
        ]);

        $this->dateService = app(ProgramDateService::class);
    }

    /**
     * Test ProgramDateService calculations with start_date = 2026-11-02 (PLAN.md §8.1, §13).
     */
    public function test_program_date_calculations_for_november_2_2026(): void
    {
        $startDate = '2026-11-02'; // Monday

        // Valid Monday check
        $validStart = $this->dateService->validateStartDate($startDate);
        $this->assertTrue($validStart->isMonday());
        $this->assertSame('2026-11-02', $validStart->toDateString());

        // Tuesday 3 Nov 2026 = Week 1, Day 2
        $week1Tue = $this->dateService->sessionDate($startDate, 1, 2);
        $this->assertSame('2026-11-03', $week1Tue->toDateString());
        $this->assertTrue($week1Tue->isTuesday());

        // Sunday 8 Nov 2026 = Week 1, Day 7
        $week1Sun = $this->dateService->sessionDate($startDate, 1, 7);
        $this->assertSame('2026-11-08', $week1Sun->toDateString());
        $this->assertTrue($week1Sun->isSunday());

        // Week 20 start date = 2027-03-15
        $week20Start = $this->dateService->weekStartDate($startDate, 20);
        $this->assertSame('2027-03-15', $week20Start->toDateString());
        $this->assertTrue($week20Start->isMonday());

        // Week 20 Sunday (10K trial) = 2027-03-21
        $week20Sun = $this->dateService->sessionDate($startDate, 20, 7);
        $this->assertSame('2027-03-21', $week20Sun->toDateString());

        // Non-Monday start date throws exception
        $this->expectException(\InvalidArgumentException::class);
        $this->dateService->validateStartDate('2026-11-03');
    }

    /**
     * Test program active status and days until start (PLAN.md §8.1).
     */
    public function test_program_active_status_relative_to_today(): void
    {
        $startDate = '2026-11-02';

        // Before start date (e.g. 2026-10-30 = 3 days before)
        $statusBefore = $this->dateService->programStatus($startDate, 20, '2026-10-30');
        $this->assertSame('upcoming', $statusBefore['status']);
        $this->assertSame(3, $statusBefore['days_until_start']);
        $this->assertSame(1, $statusBefore['active_position']);

        // Active week 1 (2026-11-04)
        $statusActive1 = $this->dateService->programStatus($startDate, 20, '2026-11-04');
        $this->assertSame('active', $statusActive1['status']);
        $this->assertSame(1, $statusActive1['active_position']);

        // Active week 5 (2026-11-30) -> 28 days diff / 7 = 4 + 1 = 5
        $statusActive5 = $this->dateService->programStatus($startDate, 20, '2026-11-30');
        $this->assertSame('active', $statusActive5['status']);
        $this->assertSame(5, $statusActive5['active_position']);

        // Completed (2027-04-01)
        $statusCompleted = $this->dateService->programStatus($startDate, 20, '2027-04-01');
        $this->assertSame('completed', $statusCompleted['status']);
    }

    /**
     * Test plan page access and display.
     */
    public function test_plan_page_displays_schedule_and_workouts(): void
    {
        $generator = app(ProgramGenerator::class);
        $program = $generator->generate($this->user, '2026-11-02');

        $response = $this->actingAs($this->user)->get('/plan/1');

        $response->assertStatus(200);
        $response->assertSee('Jadwal Latihan');
        $response->assertSee('Beban Kerja');
        $response->assertSee('Minggu 1 dari 20');
        $response->assertSee('Push');
        $response->assertSee('Long run');
        $response->assertSee('02 Nov 2026'); // Monday session date
    }

    /**
     * Test updating week notes (PLAN.md §8.10, §10.10).
     */
    public function test_can_update_week_notes(): void
    {
        $generator = app(ProgramGenerator::class);
        $program = $generator->generate($this->user, '2026-11-02');
        $week1 = $program->weeks()->where('calendar_position', 1)->first();

        $response = $this->actingAs($this->user)->patch("/program-weeks/{$week1->id}", [
            'notes' => 'Catatan adaptasi minggu pertama: kurangi intensitas di hari Jumat.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('program_weeks', [
            'id' => $week1->id,
            'notes' => 'Catatan adaptasi minggu pertama: kurangi intensitas di hari Jumat.',
        ]);
    }

    /**
     * Test repeat week shifts subsequent calendar positions and preserves records (PLAN.md §8.10, D4).
     */
    public function test_repeat_week_duplicates_week_and_shifts_subsequent_weeks(): void
    {
        $generator = app(ProgramGenerator::class);
        $program = $generator->generate($this->user, '2026-11-02');

        $week5 = $program->weeks()->where('calendar_position', 5)->first();
        $this->assertSame(5, $week5->week_number);
        $this->assertSame(5, $week5->calendar_position);
        $this->assertFalse($week5->is_repeat);

        // Pre-check count
        $this->assertSame(20, $program->weeks()->count());

        // Repeat Week 5
        $response = $this->actingAs($this->user)->post("/program-weeks/{$week5->id}/repeat");

        $response->assertRedirect(route('plan.show', ['calendar_position' => 6]));

        // Post-check: Total weeks should now be 21
        $this->assertSame(21, $program->weeks()->count());

        // The repeated week is at position 6 with week_number 5 and is_repeat = true
        $repeatedWeek = $program->weeks()->where('calendar_position', 6)->first();
        $this->assertNotNull($repeatedWeek);
        $this->assertSame(5, $repeatedWeek->week_number);
        $this->assertTrue($repeatedWeek->is_repeat);
        $this->assertSame(8, $repeatedWeek->scheduledWorkouts()->whereIn('workout_type', ['gym', 'run'])->count());
        $this->assertSame(9, $repeatedWeek->scheduledWorkouts()->count()); // 8 active + 1 rest

        // What was week 6 (calendar_position 6) should now be shifted to calendar_position 7
        $shiftedWeek6 = $program->weeks()->where('week_number', 6)->first();
        $this->assertSame(7, $shiftedWeek6->calendar_position);

        // Week 20 should now be at calendar_position 21
        $shiftedWeek20 = $program->weeks()->where('week_number', 20)->first();
        $this->assertSame(21, $shiftedWeek20->calendar_position);
    }

    /**
     * Test cancel repeat week removes repeated week and shifts back (PLAN.md §8.10).
     */
    public function test_cancel_repeat_week_succeeds_when_no_workouts_recorded(): void
    {
        $generator = app(ProgramGenerator::class);
        $program = $generator->generate($this->user, '2026-11-02');

        $week5 = $program->weeks()->where('calendar_position', 5)->first();
        $this->actingAs($this->user)->post("/program-weeks/{$week5->id}/repeat");

        $repeatedWeek = $program->weeks()->where('calendar_position', 6)->first();
        $this->assertTrue($repeatedWeek->is_repeat);

        // Cancel repeat
        $response = $this->actingAs($this->user)->delete("/program-weeks/{$repeatedWeek->id}/repeat");

        $response->assertRedirect(route('plan.show', ['calendar_position' => 5]));

        // Weeks count back to 20
        $this->assertSame(20, $program->weeks()->count());
        $this->assertDatabaseMissing('program_weeks', [
            'id' => $repeatedWeek->id,
        ]);

        // Week 6 should be back at calendar_position 6
        $shiftedWeek6 = $program->weeks()->where('week_number', 6)->first();
        $this->assertSame(6, $shiftedWeek6->calendar_position);
    }

    /**
     * Test cannot cancel repeat week if any session is completed or not planned (PLAN.md §8.10).
     */
    public function test_cannot_cancel_repeat_week_if_session_status_changed(): void
    {
        $generator = app(ProgramGenerator::class);
        $program = $generator->generate($this->user, '2026-11-02');

        $week5 = $program->weeks()->where('calendar_position', 5)->first();
        $this->actingAs($this->user)->post("/program-weeks/{$week5->id}/repeat");

        $repeatedWeek = $program->weeks()->where('calendar_position', 6)->first();

        // Mark one workout as completed
        $workout = $repeatedWeek->scheduledWorkouts()->first();
        $workout->update(['status' => 'completed']);

        // Attempt cancel repeat
        $response = $this->actingAs($this->user)->delete("/program-weeks/{$repeatedWeek->id}/repeat");

        $response->assertSessionHasErrors(['repeat']);
        $this->assertDatabaseHas('program_weeks', [
            'id' => $repeatedWeek->id,
        ]);
    }

    /**
     * Test scheduled workout status update and rescheduled_to validation (PLAN.md §8.2).
     */
    public function test_scheduled_workout_status_updates(): void
    {
        $generator = app(ProgramGenerator::class);
        $program = $generator->generate($this->user, '2026-11-02');
        $workout = ScheduledWorkout::whereHas('week', fn ($q) => $q->where('program_id', $program->id))->first();

        // 1. Update to completed
        $response = $this->actingAs($this->user)->patch("/scheduled-workouts/{$workout->id}", [
            'status' => 'completed',
        ]);
        $response->assertRedirect();
        $this->assertSame('completed', $workout->fresh()->status);

        // 2. Rescheduled requires rescheduled_to
        $responseFail = $this->actingAs($this->user)->patch("/scheduled-workouts/{$workout->id}", [
            'status' => 'rescheduled',
            'rescheduled_to' => null,
        ]);
        $responseFail->assertSessionHasErrors(['rescheduled_to']);

        // 3. Rescheduled with valid date succeeds
        $responseSuccess = $this->actingAs($this->user)->patch("/scheduled-workouts/{$workout->id}", [
            'status' => 'rescheduled',
            'rescheduled_to' => '2026-11-05',
        ]);
        $responseSuccess->assertRedirect();
        $this->assertSame('rescheduled', $workout->fresh()->status);
        $this->assertSame('2026-11-05', $workout->fresh()->rescheduled_to->format('Y-m-d'));

        // 4. Skip workout action
        $responseSkip = $this->actingAs($this->user)->post("/scheduled-workouts/{$workout->id}/skip");
        $responseSkip->assertRedirect();
        $this->assertSame('skipped', $workout->fresh()->status);
    }

    /**
     * Test user settings targets update (PLAN.md §10.11).
     */
    public function test_can_update_user_settings_targets(): void
    {
        $response = $this->actingAs($this->user)->put('/settings', [
            'height_cm' => 165.5,
            'starting_weight_kg' => 73.5,
            'target_weight_kg' => 59.0,
            'daily_steps_target' => 12000,
            'daily_calorie_target' => 2100,
            'protein_target_g' => 150,
            'carbs_target_g' => 250,
            'fat_target_g' => 60,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('user_settings', [
            'user_id' => $this->user->id,
            'height_cm' => 165.5,
            'starting_weight_kg' => 73.5,
            'target_weight_kg' => 59.0,
            'daily_steps_target' => 12000,
            'daily_calorie_target' => 2100,
            'protein_target_g' => 150,
            'carbs_target_g' => 250,
            'fat_target_g' => 60,
        ]);
    }

    /**
     * Test program start date update validates Monday (PLAN.md §8.1, §10.11).
     */
    public function test_update_program_start_date_validates_monday(): void
    {
        $generator = app(ProgramGenerator::class);
        $program = $generator->generate($this->user, '2026-11-02');

        // Tuesday (not a Monday) -> rejected
        $responseInvalid = $this->actingAs($this->user)->put('/settings/program', [
            'start_date' => '2026-11-03',
        ]);
        $responseInvalid->assertSessionHasErrors(['start_date']);
        $this->assertSame('2026-11-02', $program->fresh()->start_date->format('Y-m-d'));

        // Next Monday (2026-11-09) -> accepted
        $responseValid = $this->actingAs($this->user)->put('/settings/program', [
            'start_date' => '2026-11-09',
        ]);
        $responseValid->assertRedirect();
        $this->assertSame('2026-11-09', $program->fresh()->start_date->format('Y-m-d'));
    }
}
