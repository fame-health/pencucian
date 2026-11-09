<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WasherCommission;

class WasherCommissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, WasherCommission $washerCommission): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, WasherCommission $washerCommission): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, WasherCommission $washerCommission): bool
    {
        return $user->hasRole('admin');
    }
}
