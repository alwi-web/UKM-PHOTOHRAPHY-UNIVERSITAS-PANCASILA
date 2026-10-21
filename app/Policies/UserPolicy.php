<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminUkm();
    }

    public function view(User $user, User $model): bool
    {
        return $user->isAdminUkm();
    }

    public function create(User $user): bool
    {
        return $user->isAdminUkm();
    }

    public function update(User $user, User $model): bool
    {
        return $user->isAdminUkm();
    }

    public function delete(User $user, User $model): bool
    {
        return $user->isAdminUkm() && $user->isNot($model);
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdminUkm();
    }
}
