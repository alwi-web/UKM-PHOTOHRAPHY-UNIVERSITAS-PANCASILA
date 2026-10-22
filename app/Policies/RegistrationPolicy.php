<?php

namespace App\Policies;

use App\Models\Registration;
use App\Models\User;

/** Pendaftar calon anggota dikelola Admin UKM. Pendaftaran baru masuk dari website. */
class RegistrationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminUkm();
    }

    public function view(User $user, Registration $registration): bool
    {
        return $user->isAdminUkm();
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Registration $registration): bool
    {
        return $user->isAdminUkm();
    }

    public function delete(User $user, Registration $registration): bool
    {
        return $user->isAdminUkm();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdminUkm();
    }
}
