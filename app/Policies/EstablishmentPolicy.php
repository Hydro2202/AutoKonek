<?php

namespace App\Policies;

use App\Models\Establishment;
use App\Models\User;

class EstablishmentPolicy
{
    /** Approved listings are public; owners and admins may also see their own/all pending ones. */
    public function view(?User $user, Establishment $establishment): bool
    {
        return $establishment->isApproved()
            || ($user && ($user->isAdmin() || $user->id === $establishment->user_id));
    }

    /** Only the owning business owner may edit, manage or delete a listing. */
    public function manage(User $user, Establishment $establishment): bool
    {
        return $user->isOwner() && $user->id === $establishment->user_id;
    }
}
