<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WashTransaction;

class WashTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, WashTransaction $washTransaction): bool
    {
        return $user->hasRole('admin') || $user->id === $washTransaction->washer_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, WashTransaction $washTransaction): bool
    {
        return $user->hasRole('admin') || $user->id === $washTransaction->washer_id;
    }

    public function delete(User $user, WashTransaction $washTransaction): bool
    {
        return $user->hasRole('admin');
    }
}
