<?php

namespace App\Policies;

use App\Models\BodyMeasurement;
use App\Models\User;

class BodyMeasurementPolicy
{
    public function update(User $user, BodyMeasurement $measurement): bool
    {
        return $measurement->user_id === $user->id;
    }

    public function delete(User $user, BodyMeasurement $measurement): bool
    {
        return $measurement->user_id === $user->id;
    }
}
