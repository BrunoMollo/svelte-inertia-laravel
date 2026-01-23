<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('superadmin');
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasRole('superadmin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('superadmin');
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasRole('superadmin');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasRole('superadmin') && $user->id !== $model->id;
    }

    public function toggleStatus(User $user, User $model): bool
    {
        return $user->hasRole('superadmin') && $user->id !== $model->id;
    }

    public function resetPassword(User $user, User $model): bool
    {
        return $user->hasRole('superadmin');
    }
}
