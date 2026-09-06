<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\PointLedgerEntry;
use App\Models\User;

class PointLedgerPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('point_ledger.view_all');
        }

        return $user->isPromotor() || $user->role === UserRole::Member;
    }

    public function view(User $user, PointLedgerEntry $entry): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('point_ledger.view_all');
        }

        return (int) $entry->user_id === (int) $user->id;
    }

    public function earn(User $user): bool
    {
        return $user->isStaff() && $user->hasPermission('point_ledger.manage');
    }

    public function redeem(User $user): bool
    {
        return $user->isPromotor() || $user->role === UserRole::Member;
    }

    public function manage(User $user, ?PointLedgerEntry $entry = null): bool
    {
        return $user->isStaff() && $user->hasPermission('point_ledger.manage');
    }
}
