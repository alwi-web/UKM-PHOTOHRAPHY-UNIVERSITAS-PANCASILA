<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Item;
use App\Models\User;

class ItemPolicy
{
    private function manages(User $user): bool
    {
        return $user->hasRole(Role::AdminInventaris, Role::AdminUkm);
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Item $item): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $this->manages($user);
    }

    public function update(User $user, Item $item): bool
    {
        return $this->manages($user);
    }

    public function delete(User $user, Item $item): bool
    {
        return $this->manages($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->manages($user);
    }
}
