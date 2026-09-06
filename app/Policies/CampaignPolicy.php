<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\User;

class CampaignPolicy
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
            return $user->hasPermission('campaigns.view_all');
        }

        return $user->isPromotor();
    }

    public function view(User $user, Campaign $campaign): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('campaigns.view_all');
        }

        if ($user->isPromotor()) {
            return $campaign->launched_by_user_id === null || (int) $campaign->launched_by_user_id === (int) $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('campaigns.manage');
        }

        return $user->isPromotor();
    }

    public function update(User $user, Campaign $campaign): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('campaigns.manage');
        }

        if ($user->isPromotor()) {
            return (int) $campaign->launched_by_user_id === (int) $user->id;
        }

        return false;
    }

    public function delete(User $user, Campaign $campaign): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('campaigns.manage');
        }

        if ($user->isPromotor()) {
            return (int) $campaign->launched_by_user_id === (int) $user->id;
        }

        return false;
    }

    public function manage(User $user, ?Campaign $campaign = null): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('campaigns.manage');
        }

        if ($user->isPromotor()) {
            if ($campaign === null) {
                return true;
            }

            return $campaign->launched_by_user_id === null || (int) $campaign->launched_by_user_id === (int) $user->id;
        }

        return false;
    }
}
