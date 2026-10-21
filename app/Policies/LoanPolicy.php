<?php

namespace App\Policies;

use App\Enums\LoanStatus;
use App\Enums\Role;
use App\Models\Loan;
use App\Models\User;

class LoanPolicy
{
    public static function manages(User $user): bool
    {
        return $user->hasRole(Role::AdminInventaris, Role::AdminUkm);
    }

    private function ownsPending(User $user, Loan $loan): bool
    {
        return $loan->user_id === $user->id && $loan->status === LoanStatus::Diajukan;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Loan $loan): bool
    {
        return self::manages($user) || $loan->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Loan $loan): bool
    {
        return self::manages($user) || $this->ownsPending($user, $loan);
    }

    public function delete(User $user, Loan $loan): bool
    {
        return self::manages($user) || $this->ownsPending($user, $loan);
    }

    public function deleteAny(User $user): bool
    {
        return self::manages($user);
    }

    /** Menyetujui/menyerahkan, menolak, dan menerima pengembalian barang. */
    public function process(User $user, Loan $loan): bool
    {
        return self::manages($user);
    }
}
