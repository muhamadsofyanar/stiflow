<?php

namespace App\Policies;

use App\Enums\PayoutStatus;
use App\Enums\UserRole;
use App\Models\Payout;
use App\Models\User;

class PayoutPolicy
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
            return $user->hasPermission('payouts.view_all');
        }

        return $user->isPromotor();
    }

    public function view(User $user, Payout $payout): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('payouts.view_all');
        }

        if ($user->isPromotor()) {
            $pid = $user->promoterProfile?->id;
            if ($pid === null) {
                return false;
            }

            return $payout->commissionEntries()
                ->where('beneficiary_promoter_profile_id', $pid)
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->hasPermission('payouts.manage');
    }

    public function update(User $user, Payout $payout): bool
    {
        if ($payout->status === PayoutStatus::Locked || $payout->locked_at !== null) {
            return false;
        }

        return $user->isStaff() && $user->hasPermission('payouts.manage');
    }

    public function delete(User $user, Payout $payout): bool
    {
        if ($payout->status === PayoutStatus::Locked || $payout->locked_at !== null) {
            return false;
        }

        return $user->isStaff() && $user->hasPermission('payouts.manage');
    }

    public function approve(User $user, Payout $payout): bool
    {
        return $user->isStaff() && $user->hasPermission('payouts.approve');
    }

    public function paid(User $user, Payout $payout): bool
    {
        return $user->isStaff() && $user->hasPermission('payouts.mark_paid');
    }

    public function manage(User $user, ?Payout $payout = null): bool
    {
        return $user->isStaff() && $user->hasPermission('payouts.manage');
    }
}
