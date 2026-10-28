<?php

namespace App\Policies;

use App\Models\Item;
use App\Models\User;

class ItemPolicy
{
    /** Peminjam perlu melihat daftar barang untuk tahu apa yang tersedia. */
    public function viewAny(User $user): bool
    {
        return $user->role->canBorrow();
    }

    public function view(User $user, Item $item): bool
    {
        return $user->role->canBorrow();
    }

    public function create(User $user): bool
    {
        return $user->role->canManageInventory();
    }

    public function update(User $user, Item $item): bool
    {
        return $user->role->canManageInventory();
    }

    public function delete(User $user, Item $item): bool
    {
        return $user->role->canManageInventory();
    }

    public function deleteAny(User $user): bool
    {
        return $user->role->canManageInventory();
    }
}
