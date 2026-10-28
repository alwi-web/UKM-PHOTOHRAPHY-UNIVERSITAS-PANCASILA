<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

/** Kategori galeri dikelola Admin UKM. */
class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminUkm();
    }

    public function view(User $user, Category $category): bool
    {
        return $user->isAdminUkm();
    }

    public function create(User $user): bool
    {
        return $user->isAdminUkm();
    }

    public function update(User $user, Category $category): bool
    {
        return $user->isAdminUkm();
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->isAdminUkm();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdminUkm();
    }
}
