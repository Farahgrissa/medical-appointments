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
}
