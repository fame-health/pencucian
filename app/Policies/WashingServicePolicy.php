<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WashingService;

class WashingServicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, WashingService $washingService): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, WashingService $washingService): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, WashingService $washingService): bool
    {
        return $user->hasRole('admin');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
