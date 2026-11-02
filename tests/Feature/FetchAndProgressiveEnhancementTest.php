<?php

namespace Tests\Feature;

use App\Models\BodyMeasurement;
use App\Models\DailyActivity;
use App\Models\Program;
use App\Models\ProgramWeek;
use App\Models\ScheduledWorkout;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FetchAndProgressiveEnhancementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        UserSetting::create([
            'user_id' => $this->user->id,
            'start_date' => '2026-11-02',
            'timezone' => 'Asia/Jakarta',
        ]);

        $this->otherUser = User::factory()->create();
        UserSetting::create([
            'user_id' => $this->otherUser->id,
            'start_date' => '2026-11-02',
            'timezone' => 'Asia/Jakarta',
        ]);
    }

    public function test_scheduled_workout_status_update_supports_json_fetch(): void
    {
        $program = Program::create([
            'user_id' => $this->user->id,
            'name' => 'Program 20 Minggu',
            'start_date' => '2026-11-02',
        ]);

        $week = ProgramWeek::create([
            'program_id' => $program->id,
            'week_number' => 1,
            'calendar_position' => 1,
            'phase_label' => 'base',
        ]);

        $workout = ScheduledWorkout::create([
            'program_week_id' => $week->id,
            'weekday' => 1,
            'sort_order' => 1,
            'workout_type' => 'gym',
            'title' => 'Upper A',
            'status' => 'planned',
        ]);

        // Alpine Fetch: Expects JSON
        $response = $this->actingAs($this->user)
            ->patchJson("/scheduled-workouts/{$workout->id}", [
                'status' => 'completed',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Tersimpan',
                'scheduled_workout' => [
                    'id' => $workout->id,
                    'status' => 'completed',
                    'status_label' => 'Selesai',
                ],
            ]);

        $this->assertDatabaseHas('scheduled_workouts', [
            'id' => $workout->id,
            'status' => 'completed',
        ]);
    }

    public function test_scheduled_workout_status_update_falls_back_to_redirect_without_json(): void
    {
        $program = Program::create([
            'user_id' => $this->user->id,
            'name' => 'Program 20 Minggu',
            'start_date' => '2026-11-02',
        ]);

        $week = ProgramWeek::create([
            'program_id' => $program->id,
            'week_number' => 1,
            'calendar_position' => 1,
            'phase_label' => 'base',
        ]);

        $workout = ScheduledWorkout::create([
            'program_week_id' => $week->id,
            'weekday' => 1,
            'sort_order' => 1,
            'workout_type' => 'gym',
            'title' => 'Upper A',
            'status' => 'planned',
        ]);

        // Standard Form submit (No JSON header): Returns redirect
        $response = $this->actingAs($this->user)
            ->patch("/scheduled-workouts/{$workout->id}", [
                'status' => 'completed',
            ]);

        $response->assertRedirect();
    }

    public function test_scheduled_workout_skip_supports_json_fetch(): void
    {
        $program = Program::create([
            'user_id' => $this->user->id,
            'name' => 'Program 20 Minggu',
            'start_date' => '2026-11-02',
        ]);

        $week = ProgramWeek::create([
            'program_id' => $program->id,
            'week_number' => 1,
            'calendar_position' => 1,
            'phase_label' => 'base',
        ]);

        $workout = ScheduledWorkout::create([
            'program_week_id' => $week->id,
            'weekday' => 2,
            'sort_order' => 1,
            'workout_type' => 'run',
            'title' => 'Easy Run 5K',
            'status' => 'planned',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/scheduled-workouts/{$workout->id}/skip");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Tersimpan',
                'scheduled_workout' => [
                    'id' => $workout->id,
                    'status' => 'skipped',
                    'status_label' => 'Dilewati',
                ],
            ]);

        $this->assertDatabaseHas('scheduled_workouts', [
            'id' => $workout->id,
            'status' => 'skipped',
        ]);
    }

    public function test_scheduled_workout_denies_other_user(): void
    {
        $program = Program::create([
            'user_id' => $this->otherUser->id,
            'name' => 'Program 20 Minggu',
            'start_date' => '2026-11-02',
        ]);

        $week = ProgramWeek::create([
            'program_id' => $program->id,
            'week_number' => 1,
            'calendar_position' => 1,
            'phase_label' => 'base',
        ]);

        $workout = ScheduledWorkout::create([
            'program_week_id' => $week->id,
            'weekday' => 1,
            'sort_order' => 1,
            'workout_type' => 'gym',
            'title' => 'Upper A',
            'status' => 'planned',
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/scheduled-workouts/{$workout->id}", [
                'status' => 'completed',
            ]);

        $response->assertNotFound();
    }

    public function test_daily_activity_upsert_supports_json_fetch(): void
    {
        $response = $this->actingAs($this->user)
            ->putJson('/daily-activities/2026-11-02', [
                'steps' => '8.500',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Tersimpan',
                'steps' => 8500,
                'formatted_steps' => '8.500',
                'activity_date' => '2026-11-02',
            ]);

        $activity = DailyActivity::where('user_id', $this->user->id)->first();
        $this->assertNotNull($activity);
        $this->assertSame('2026-11-02', $activity->activity_date->toDateString());
        $this->assertSame(8500, $activity->steps);
    }

    public function test_daily_activity_upsert_validates_json_request(): void
    {
        $response = $this->actingAs($this->user)
            ->putJson('/daily-activities/2026-11-02', [
                'steps' => '300.000', // exceeds limit
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['steps']);
    }

    public function test_daily_activity_upsert_falls_back_to_redirect_without_json(): void
    {
        $response = $this->actingAs($this->user)
            ->put('/daily-activities/2026-11-02', [
                'steps' => '8.500',
            ]);

        $response->assertRedirect();
    }

    public function test_body_measurement_upsert_supports_json_fetch(): void
    {
        $response = $this->actingAs($this->user)
            ->putJson('/body/2026-11-02', [
                'weight_kg' => '74,5',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Tersimpan',
                'measured_at' => '2026-11-02',
                'weight_kg' => 74.5,
                'formatted_weight' => '74,5',
            ]);

        $measurement = BodyMeasurement::where('user_id', $this->user->id)->first();
        $this->assertNotNull($measurement);
        $this->assertSame('2026-11-02', $measurement->measured_at->toDateString());
        $this->assertEquals(74.5, (float) $measurement->weight_kg);
    }

    public function test_body_measurement_upsert_validates_json_request(): void
    {
        $response = $this->actingAs($this->user)
            ->putJson('/body/2026-11-02', [
                'weight_kg' => '10', // < 20kg limit
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['weight_kg']);
    }
}
