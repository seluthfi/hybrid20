<?php

namespace App\Policies;

use App\Models\Food;
use App\Models\User;

class FoodPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Food $food): bool
    {
        return $food->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Food $food): bool
    {
        return $food->user_id === $user->id;
    }

    public function delete(User $user, Food $food): bool
    {
        return $food->user_id === $user->id;
    }
}
