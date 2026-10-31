<?php
namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function update(User $authUser, User $user)
    {
        // Seul l’utilisateur peut modifier son profil
        return $authUser->id === $user->id;
    }

    /**
     * Admin: create doctor accounts.
     */
    public function create(User $authUser): bool
    {
        return $authUser->can('manage-users');
    }

    /**
     * Admin moderation: deactivate / reactivate a doctor or a patient.
     * Admin accounts (including one's own) cannot be deactivated from the UI.
     */
    public function moderate(User $authUser, User $user): bool
    {
        return $authUser->can('manage-users')
            && $authUser->id !== $user->id
            && ! $user->hasRole('admin');
    }
}
