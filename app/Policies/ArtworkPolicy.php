<?php

namespace App\Policies;

use App\Enums\ArtworkStatus;
use App\Models\Artwork;
use App\Models\User;

class ArtworkPolicy
{
    public static function curates(User $user): bool
    {
        return $user->role->canCurate();
    }

    private function ownsPending(User $user, Artwork $artwork): bool
    {
        return $artwork->user_id === $user->id && $artwork->status === ArtworkStatus::Pending;
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

    /** Member hanya bisa mengubah karyanya yang belum dikurasi; kurator hanya mengkurasi. */
    public function update(User $user, Artwork $artwork): bool
    {
        return $user->isAdminUkm() || $this->ownsPending($user, $artwork);
    }

    public function delete(User $user, Artwork $artwork): bool
    {
        return $user->isAdminUkm() || $this->ownsPending($user, $artwork);
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
