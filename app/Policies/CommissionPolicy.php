<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\CommissionEntry;
use App\Models\User;

class CommissionPolicy
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
            return $user->hasPermission('commissions.view_all');
        }

        return $user->isPromotor();
    }

    public function view(User $user, CommissionEntry $entry): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('commissions.view_all');
        }

        if ($user->isPromotor()) {
            $pid = $user->promoterProfile?->id;

            return $pid !== null && (int) $entry->beneficiary_promoter_profile_id === (int) $pid;
        }

        return false;
    }

    public function manage(User $user, ?CommissionEntry $entry = null): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('commissions.manage');
        }

        if ($user->isPromotor()) {
            if ($entry === null) {
                return false;
            }
            $pid = $user->promoterProfile?->id;

            return $pid !== null && (int) $entry->beneficiary_promoter_profile_id === (int) $pid;
        }

        return false;
    }
}
