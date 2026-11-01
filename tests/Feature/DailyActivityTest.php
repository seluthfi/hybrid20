<?php

namespace Tests\Feature;

use App\Models\DailyActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upsert_daily_steps(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->put('/daily-activities/2026-11-03', [
                'steps' => '10.500',
            ]);

        $response->assertRedirect();
        
        $activity = DailyActivity::where('user_id', $user->id)->first();
        $this->assertNotNull($activity);
        $this->assertSame('2026-11-03', $activity->activity_date->toDateString());
        $this->assertSame(10500, $activity->steps);

        // Updating on same date replaces the value (single row per date, R5 / §8.6)
        $this->actingAs($user)
            ->put('/daily-activities/2026-11-03', [
                'steps' => '12.000',
            ]);

        $this->assertSame(1, DailyActivity::where('user_id', $user->id)->whereDate('activity_date', '2026-11-03')->count());
        $activity->refresh();
        $this->assertSame(12000, $activity->steps);
    }

    public function test_user_can_upsert_body_weight(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->put('/body/2026-11-03', [
                'weight_kg' => '74,5',
            ]);

        $response->assertRedirect();
        
        $measurement = \App\Models\BodyMeasurement::where('user_id', $user->id)->first();
        $this->assertNotNull($measurement);
        $this->assertSame('2026-11-03', $measurement->measured_at->toDateString());
        $this->assertEquals(74.5, (float) $measurement->weight_kg);

        // Update on same date replaces
        $this->actingAs($user)
            ->put('/body/2026-11-03', [
                'weight_kg' => '74.2',
            ]);

        $this->assertSame(1, \App\Models\BodyMeasurement::where('user_id', $user->id)->whereDate('measured_at', '2026-11-03')->count());
        $measurement->refresh();
        $this->assertEquals(74.2, (float) $measurement->weight_kg);
    }
}
