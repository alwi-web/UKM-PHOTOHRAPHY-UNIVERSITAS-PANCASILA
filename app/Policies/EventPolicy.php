<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Event $event): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdminUkm();
    }

    public function update(User $user, Event $event): bool
    {
        return $user->isAdminUkm();
    }

    public function delete(User $user, Event $event): bool
    {
        return $user->isAdminUkm();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdminUkm();
    }

    public function join(User $user, Event $event): bool
    {
        return $event->canRegister($user);
    }
}
