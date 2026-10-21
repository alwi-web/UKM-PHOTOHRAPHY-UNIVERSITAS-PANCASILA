<?php

namespace App\Policies;

use App\Enums\ArtworkStatus;
use App\Enums\Role;
use App\Models\Artwork;
use App\Models\User;

class ArtworkPolicy
{
    public static function curates(User $user): bool
    {
        return $user->hasRole(Role::Kurator, Role::AdminUkm);
    }

    public function viewAny(User $user): bool
    {
        return $user->role->canUploadArtwork() || self::curates($user);
    }

    public function view(User $user, Artwork $artwork): bool
    {
        return self::curates($user) || $artwork->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->role->canUploadArtwork();
    }

    /** Pemilik hanya bisa mengubah karya yang belum dikurasi. */
    public function update(User $user, Artwork $artwork): bool
    {
        return self::curates($user)
            || ($artwork->user_id === $user->id && $artwork->status === ArtworkStatus::Pending);
    }

    public function delete(User $user, Artwork $artwork): bool
    {
        return $user->isAdminUkm() || $artwork->user_id === $user->id;
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdminUkm();
    }

    public function curate(User $user, Artwork $artwork): bool
    {
        return self::curates($user);
    }
}
