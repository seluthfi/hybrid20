<?php

namespace App\Policies;

use App\Models\ProgressPhoto;
use App\Models\User;

class ProgressPhotoPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ProgressPhoto $progressPhoto): bool
    {
        return $progressPhoto->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ProgressPhoto $progressPhoto): bool
    {
        return $progressPhoto->user_id === $user->id;
    }

    public function delete(User $user, ProgressPhoto $progressPhoto): bool
    {
        return $progressPhoto->user_id === $user->id;
    }
}
