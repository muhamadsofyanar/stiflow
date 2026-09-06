<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\ReferralLink;
use App\Models\User;

class ReferralLinkPolicy
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
            return $user->hasPermission('referral_links.view_all');
        }

        return $user->isPromotor();
    }

    public function view(User $user, ReferralLink $link): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('referral_links.view_all');
        }

        if ($user->isPromotor()) {
            $pid = $user->promoterProfile?->id;

            return $pid !== null && (int) $link->promoter_profile_id === (int) $pid;
        }

        return false;
    }

    public function create(User $user): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('referral_links.manage');
        }

        return $user->isPromotor();
    }

    public function update(User $user, ReferralLink $link): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('referral_links.manage');
        }

        if ($user->isPromotor()) {
            $pid = $user->promoterProfile?->id;

            return $pid !== null && (int) $link->promoter_profile_id === (int) $pid;
        }

        return false;
    }

    public function delete(User $user, ReferralLink $link): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('referral_links.manage');
        }

        if ($user->isPromotor()) {
            $pid = $user->promoterProfile?->id;

            return $pid !== null && (int) $link->promoter_profile_id === (int) $pid;
        }

        return false;
    }

    public function manage(User $user, ?ReferralLink $link = null): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('referral_links.manage');
        }

        if ($user->isPromotor()) {
            if ($link === null) {
                return true;
            }
            $pid = $user->promoterProfile?->id;

            return $pid !== null && (int) $link->promoter_profile_id === (int) $pid;
        }

        return false;
    }
}
