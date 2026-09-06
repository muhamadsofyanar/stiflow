<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\ProductLicenseKey;
use App\Models\User;

class LicenseKeyPolicy
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
            return $user->hasPermission('license_keys.view_all');
        }

        return $user->isPromotor() || $user->role === UserRole::Member;
    }

    public function view(User $user, ProductLicenseKey $key): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('license_keys.view_all');
        }

        if ($key->assigned_to_user_id !== null) {
            return (int) $key->assigned_to_user_id === (int) $user->id;
        }

        if ($user->isPromotor()) {
            return (int) $key->imported_by_user_id === (int) $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->hasPermission('license_keys.manage');
    }

    public function update(User $user, ProductLicenseKey $key): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('license_keys.manage');
        }

        return false;
    }

    public function manage(User $user, ?ProductLicenseKey $key = null): bool
    {
        return $user->isStaff() && $user->hasPermission('license_keys.manage');
    }
}
