<?php

namespace App\Policies;

use App\Models\Slot;
use App\Models\User;

class SlotPolicy
{
    public function create(User $user): bool
    {
        return $user->can('create-slot');
    }

    /**
     * A doctor can only delete their own slots, and only while nobody has booked them.
     */
    public function delete(User $user, Slot $slot): bool
    {
        return $user->can('delete-slot')
            && $user->id === $slot->doctor_id
            && $slot->isAvailable();
    }
}
