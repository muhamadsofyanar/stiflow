<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\IntegrationConnection;
use App\Models\User;

class IntegrationConnectionPolicy
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
            return $user->hasPermission('integration_connections.view_all');
        }

        return $user->isPromotor();
    }

    public function view(User $user, IntegrationConnection $connection): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('integration_connections.view_all');
        }

        if ($user->isPromotor()) {
            return $connection->owned_by_user_id === null || (int) $connection->owned_by_user_id === (int) $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('integration_connections.manage');
        }

        return $user->isPromotor();
    }

    public function update(User $user, IntegrationConnection $connection): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('integration_connections.manage');
        }

        if ($user->isPromotor()) {
            return (int) $connection->owned_by_user_id === (int) $user->id;
        }

        return false;
    }

    public function delete(User $user, IntegrationConnection $connection): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('integration_connections.manage');
        }

        if ($user->isPromotor()) {
            return (int) $connection->owned_by_user_id === (int) $user->id;
        }

        return false;
    }

    public function manage(User $user, ?IntegrationConnection $connection = null): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('integration_connections.manage');
        }

        if ($user->isPromotor()) {
            if ($connection === null) {
                return true;
            }

            return $connection->owned_by_user_id === null || (int) $connection->owned_by_user_id === (int) $user->id;
        }

        return false;
    }
}
