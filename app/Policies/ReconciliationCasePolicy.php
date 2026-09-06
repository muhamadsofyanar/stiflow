<?php

namespace App\Policies;

use App\Models\ReconciliationCase;
use App\Models\User;

class ReconciliationCasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaffOrAbove();
    }

    public function view(User $user, ReconciliationCase $case): bool
    {
        return $user->isStaffOrAbove();
    }

    public function resolve(User $user): bool
    {
        return $user->isAdmin();
    }

    public function manage(User $user): bool
    {
        return $user->isStaffOrAbove();
    }
}
