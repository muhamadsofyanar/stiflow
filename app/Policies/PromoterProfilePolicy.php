<?php

namespace App\Policies;

use App\Models\PromoterProfile;
use App\Models\User;

class PromoterProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaffOrAbove();
    }

    public function view(User $user, PromoterProfile $profile): bool
    {
        if ($user->isStaffOrAbove()) {
            return true;
        }

        return (int) $user->id === (int) $profile->user_id;
    }

    public function edit(User $user): bool
    {
        return $user->isStaffOrAbove();
    }

    public function verify(User $user): bool
    {
        return $user->isAdmin();
    }

    public function manage(User $user): bool
    {
        return $user->isStaffOrAbove();
    }
}
