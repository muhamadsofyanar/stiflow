<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\StifinResult;
use App\Models\User;

class StifinResultPolicy
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
            return $user->hasPermission('stifin_results.view_all');
        }

        return $user->isPromotor() || $user->role === UserRole::Member;
    }

    public function view(User $user, StifinResult $result): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('stifin_results.view_all');
        }

        if ($user->isPromotor()) {
            $pid = $user->promoterProfile?->id;

            return $pid !== null && (int) $result->promoter_owner_profile_id === (int) $pid;
        }

        if ($user->role === UserRole::Member) {
            return (int) $result->member_user_id === (int) $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('stifin_results.manage');
        }

        return $user->isPromotor();
    }

    public function update(User $user, StifinResult $result): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('stifin_results.manage');
        }

        if ($user->isPromotor()) {
            $pid = $user->promoterProfile?->id;

            return $pid !== null && (int) $result->promoter_owner_profile_id === (int) $pid;
        }

        return false;
    }

    public function manage(User $user, ?StifinResult $result = null): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('stifin_results.manage');
        }

        if ($user->isPromotor()) {
            if ($result === null) {
                return true;
            }
            $pid = $user->promoterProfile?->id;

            return $pid !== null && (int) $result->promoter_owner_profile_id === (int) $pid;
        }

        return false;
    }
}
