<?php

namespace Tests\Feature;

use App\Models\BodyMeasurement;
use App\Models\DailyActivity;
use App\Models\Program;
use App\Models\ProgramWeek;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseConstraintsTest extends TestCase
{
    use RefreshDatabase;

    public function test_program_weeks_unique_calendar_position_per_program(): void
    {
        $program = Program::factory()->create();

        ProgramWeek::factory()->create([
            'program_id' => $program->id,
            'week_number' => 1,
            'calendar_position' => 1,
        ]);

        $this->expectException(QueryException::class);

        // Duplikasi calendar_position pada program yang sama harus memicu QueryException
        ProgramWeek::factory()->create([
            'program_id' => $program->id,
            'week_number' => 2,
            'calendar_position' => 1,
        ]);
    }

    public function test_body_measurements_unique_measured_at_per_user(): void
    {
        $user = User::factory()->create();

        BodyMeasurement::factory()->create([
            'user_id' => $user->id,
            'measured_at' => '2026-11-02',
            'weight_kg' => 74.00,
        ]);

        $this->expectException(QueryException::class);

        // Duplikasi measured_at untuk user yang sama harus ditolak
        BodyMeasurement::factory()->create([
            'user_id' => $user->id,
            'measured_at' => '2026-11-02',
            'weight_kg' => 73.80,
        ]);
    }

    public function test_daily_activities_unique_activity_date_per_user(): void
    {
        $user = User::factory()->create();

        DailyActivity::factory()->create([
            'user_id' => $user->id,
            'activity_date' => '2026-11-02',
            'steps' => 8000,
        ]);

        $this->expectException(QueryException::class);

        // Duplikasi activity_date untuk user yang sama harus ditolak
        DailyActivity::factory()->create([
            'user_id' => $user->id,
            'activity_date' => '2026-11-02',
            'steps' => 9500,
        ]);
    }

    public function test_user_settings_unique_user_id(): void
    {
        $user = User::factory()->create();

        UserSetting::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->expectException(QueryException::class);

        // Duplikasi user_id pada user_settings harus ditolak
        UserSetting::factory()->create([
            'user_id' => $user->id,
        ]);
    }
}
