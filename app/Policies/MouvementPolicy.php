<?php

namespace App\Policies;

use App\Models\Mouvement;
use App\Models\User;

class MouvementPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Mouvement $mouvement): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        // Admin et opérateur peuvent créer des mouvements.
        return true;
    }

    public function update(User $user, Mouvement $mouvement): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Mouvement $mouvement): bool
    {
        return $user->isAdmin();
    }
}
