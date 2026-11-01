<?php

namespace Tests\Feature;

use App\Models\DailyActivity;
use App\Models\Program;
use App\Models\ProgramWeek;
use App\Models\ScheduledWorkout;
use App\Models\User;
use App\Models\UserSetting;
use App\Models\Workout;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkoutFase4ComprehensiveTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test timezone handling for 23:30 WIB and 00:30 WIB boundary (PLAN.md §8.9).
     * 23:30 WIB must result in performed_on = date 1.
     * 00:30 WIB must result in performed_on = date 2.
     * Both stored in UTC performed_at.
     */
    public function test_timezone_boundary_at_2330_and_0030_wib(): void
    {
        $user = User::factory()->create();
        UserSetting::factory()->create([
            'user_id' => $user->id,
            'timezone' => 'Asia/Jakarta', // UTC+7
        ]);

        // 1. Session at 23:30 WIB on 2026-11-03 (UTC = 2026-11-03 16:30)
        $this->actingAs($user)->post(route('workouts.store'), [
            'workout_type' => 'run',
            'title' => 'Lari Larut Malam',
            'performed_at' => '2026-11-03T23:30',
            'distance_km' => '3,0',
            'duration' => '18:00',
        ]);

        $workout1 = Workout::where('title', 'Lari Larut Malam')->first();
        $this->assertNotNull($workout1);
        $this->assertSame('2026-11-03', $workout1->performed_on->toDateString());
        // In UTC, 23:30 WIB is 16:30 UTC
        $this->assertSame('16:30:00', $workout1->performed_at->format('H:i:s'));

        // 2. Session at 00:30 WIB on 2026-11-04 (UTC = 2026-11-03 17:30)
        $this->actingAs($user)->post(route('workouts.store'), [
            'workout_type' => 'run',
            'title' => 'Lari Dini Hari',
            'performed_at' => '2026-11-04T00:30',
            'distance_km' => '3,0',
            'duration' => '18:00',
        ]);

        $workout2 = Workout::where('title', 'Lari Dini Hari')->first();
        $this->assertNotNull($workout2);
        $this->assertSame('2026-11-04', $workout2->performed_on->toDateString());
        // In UTC, 00:30 WIB is 17:30 UTC of previous day
        $this->assertSame('17:30:00', $workout2->performed_at->format('H:i:s'));
    }

    /**
     * Test duration >= 60 minutes parsing (PLAN.md §8.4).
     */
    public function test_duration_greater_than_or_equal_to_60_minutes(): void
    {
        $user = User::factory()->create();

        // 75 minutes 30 seconds = (75 * 60) + 30 = 4530 seconds
        $this->actingAs($user)->post(route('workouts.store'), [
            'workout_type' => 'run',
            'title' => 'Long Run 75m',
            'performed_at' => '2026-11-08T06:00',
            'distance_km' => '10,0',
            'duration' => '75:30',
        ]);

        $workout = Workout::where('title', 'Long Run 75m')->first();
        $this->assertNotNull($workout);
        $this->assertSame(4530, $workout->duration_seconds);
        // 4530 / 10 = 453 seconds/km -> 7'33"/km
        $this->assertSame(453, $workout->pace_seconds_per_km);
    }

    /**
     * Test Tuesday gym + run dual session pair (PLAN.md §6.1, §6.2, §13).
     * Both sessions can be logged on the same day independently.
     */
    public function test_tuesday_gym_and_run_pair_on_same_day(): void
    {
        $user = User::factory()->create();
        $program = Program::factory()->create([
            'user_id' => $user->id,
            'start_date' => '2026-11-02', // Monday
            'status' => 'active',
        ]);

        $week = ProgramWeek::factory()->create([
            'program_id' => $program->id,
            'calendar_position' => 1,
            'week_number' => 1,
        ]);

        // Tuesday Gym (sort_order = 1)
        $tuesdayGym = ScheduledWorkout::factory()->create([
            'program_week_id' => $week->id,
            'weekday' => 2,
            'workout_type' => 'gym',
            'title' => 'Back / Biceps / Traps',
            'sort_order' => 1,
            'status' => 'planned',
        ]);

        // Tuesday Run (sort_order = 2)
        $tuesdayRun = ScheduledWorkout::factory()->create([
            'program_week_id' => $week->id,
            'weekday' => 2,
            'workout_type' => 'run',
            'title' => 'Easy Run 2.5K',
            'planned_distance_km' => 2.5,
            'sort_order' => 2,
            'status' => 'planned',
        ]);

        // Log Gym
        $this->actingAs($user)->post(route('workouts.store'), [
            'workout_type' => 'gym',
            'scheduled_workout_id' => $tuesdayGym->id,
            'title' => 'Back / Biceps / Traps',
            'performed_at' => '2026-11-03T07:00',
            'duration' => '45:00',
            'status' => 'completed',
        ]);

        $tuesdayGym->refresh();
        $tuesdayRun->refresh();
        $this->assertSame('completed', $tuesdayGym->status);
        $this->assertSame('planned', $tuesdayRun->status);

        // Log Run on the same Tuesday
        $this->actingAs($user)->post(route('workouts.store'), [
            'workout_type' => 'run',
            'scheduled_workout_id' => $tuesdayRun->id,
            'title' => 'Easy Run 2.5K',
            'performed_at' => '2026-11-03T17:00',
            'distance_km' => '2,5',
            'duration' => '15:00',
            'status' => 'completed',
        ]);

        $tuesdayRun->refresh();
        $this->assertSame('completed', $tuesdayRun->status);

        // Verify both workouts exist for the same day
        $tuesdayWorkouts = Workout::where('user_id', $user->id)
            ->whereDate('performed_on', '2026-11-03')
            ->get();

        $this->assertCount(2, $tuesdayWorkouts);
        $this->assertTrue($tuesdayWorkouts->contains('workout_type', 'gym'));
        $this->assertTrue($tuesdayWorkouts->contains('workout_type', 'run'));
    }

    /**
     * Test cross-user authorization policy (PLAN.md R7, §12).
     */
    public function test_cross_user_policy_protection(): void
    {
        $userAlice = User::factory()->create();
        $userBob = User::factory()->create();

        $aliceWorkout = Workout::factory()->create([
            'user_id' => $userAlice->id,
            'title' => 'Rahasia Alice',
        ]);

        // Bob cannot view Alice's workout
        $this->actingAs($userBob)->get(route('workouts.show', $aliceWorkout->id))->assertNotFound();

        // Bob cannot edit Alice's workout
        $this->actingAs($userBob)->get(route('workouts.edit', $aliceWorkout->id))->assertNotFound();

        // Bob cannot update Alice's workout
        $this->actingAs($userBob)->put(route('workouts.update', $aliceWorkout->id), [
            'title' => 'Diretas Bob',
        ])->assertNotFound();

        // Bob cannot delete Alice's workout
        $this->actingAs($userBob)->delete(route('workouts.destroy', $aliceWorkout->id))->assertNotFound();

        // Alice's workout remains unchanged
        $aliceWorkout->refresh();
        $this->assertSame('Rahasia Alice', $aliceWorkout->title);
    }

    /**
     * Test resetting scheduled workout status when one of multiple workouts is deleted (Rule D23).
     */
    public function test_reset_status_only_when_no_other_workouts_attached(): void
    {
        $user = User::factory()->create();
        $scheduled = ScheduledWorkout::factory()->create([
            'status' => 'completed',
        ]);

        // 2 workouts attached to same scheduled workout
        $w1 = Workout::factory()->create([
            'user_id' => $user->id,
            'scheduled_workout_id' => $scheduled->id,
        ]);
        $w2 = Workout::factory()->create([
            'user_id' => $user->id,
            'scheduled_workout_id' => $scheduled->id,
        ]);

        // Delete first workout -> scheduled workout status remains completed because $w2 exists
        $this->actingAs($user)->delete(route('workouts.destroy', $w1->id));
        $scheduled->refresh();
        $this->assertSame('completed', $scheduled->status);

        // Delete second workout -> scheduled workout status reverts to planned
        $this->actingAs($user)->delete(route('workouts.destroy', $w2->id));
        $scheduled->refresh();
        $this->assertSame('planned', $scheduled->status);
    }
}
