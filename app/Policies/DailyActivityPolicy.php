<?php

namespace App\Policies;

use App\Models\DailyActivity;
use App\Models\User;

class DailyActivityPolicy
{
    public function update(User $user, DailyActivity $activity): bool
    {
        return $activity->user_id === $user->id;
    }

    public function delete(User $user, DailyActivity $activity): bool
    {
        return $activity->user_id === $user->id;
    }
}
