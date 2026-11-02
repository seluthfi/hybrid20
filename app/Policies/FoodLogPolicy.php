<?php

namespace App\Policies;

use App\Models\FoodLog;
use App\Models\User;

class FoodLogPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, FoodLog $foodLog): bool
    {
        return $foodLog->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, FoodLog $foodLog): bool
    {
        return $foodLog->user_id === $user->id;
    }

    public function delete(User $user, FoodLog $foodLog): bool
    {
        return $foodLog->user_id === $user->id;
    }
}
