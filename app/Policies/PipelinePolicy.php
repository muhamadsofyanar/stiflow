<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Pipeline;
use App\Models\User;

class PipelinePolicy
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
        return $user->isStaffOrAbove() || $user->isPromotor();
    }

    public function view(User $user, Pipeline $pipeline): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('pipelines.view');
        }

        if ($user->isPromotor()) {
            return $pipeline->owner_user_id === null || (int) $pipeline->owner_user_id === (int) $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isStaffOrAbove() || $user->isPromotor();
    }

    public function update(User $user, Pipeline $pipeline): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('pipelines.manage');
        }

        if ($user->isPromotor()) {
            return $pipeline->owner_user_id === null || (int) $pipeline->owner_user_id === (int) $user->id;
        }

        return false;
    }

    public function delete(User $user, Pipeline $pipeline): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('pipelines.manage');
        }

        if ($user->isPromotor()) {
            return (int) $pipeline->owner_user_id === (int) $user->id;
        }

        return false;
    }

    public function manage(User $user, ?Pipeline $pipeline = null): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('pipelines.manage');
        }

        if ($user->isPromotor()) {
            if ($pipeline === null) {
                return true;
            }

            return $pipeline->owner_user_id === null || (int) $pipeline->owner_user_id === (int) $user->id;
        }

        return false;
    }
}
