<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\DigitalAsset;
use App\Models\User;

class DigitalAssetPolicy
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
            return $user->hasPermission('digital_assets.view_all');
        }

        return (bool) $user;
    }

    public function view(User $user, DigitalAsset $asset): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('digital_assets.view_all');
        }

        if ($asset->is_published) {
            return (bool) $user;
        }

        if ($user->isPromotor()) {
            return $asset->uploaded_by_user_id === null || (int) $asset->uploaded_by_user_id === (int) $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->hasPermission('digital_assets.manage');
    }

    public function update(User $user, DigitalAsset $asset): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('digital_assets.manage');
        }

        if ($user->isPromotor()) {
            return (int) $asset->uploaded_by_user_id === (int) $user->id;
        }

        return false;
    }

    public function delete(User $user, DigitalAsset $asset): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('digital_assets.manage');
        }

        if ($user->isPromotor()) {
            return (int) $asset->uploaded_by_user_id === (int) $user->id;
        }

        return false;
    }

    public function manage(User $user, ?DigitalAsset $asset = null): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('digital_assets.manage');
        }

        if ($user->isPromotor()) {
            if ($asset === null) {
                return false;
            }

            return (int) $asset->uploaded_by_user_id === (int) $user->id;
        }

        return false;
    }
}
