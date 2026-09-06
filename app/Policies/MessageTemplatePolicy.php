<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\MessageTemplate;
use App\Models\User;

class MessageTemplatePolicy
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
            return $user->hasPermission('message_templates.view_all');
        }

        return $user->isPromotor();
    }

    public function view(User $user, MessageTemplate $template): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('message_templates.view_all');
        }

        if ($user->isPromotor()) {
            return $template->created_by_user_id === null || (int) $template->created_by_user_id === (int) $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('message_templates.manage');
        }

        return $user->isPromotor();
    }

    public function update(User $user, MessageTemplate $template): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('message_templates.manage');
        }

        if ($user->isPromotor()) {
            return (int) $template->created_by_user_id === (int) $user->id;
        }

        return false;
    }

    public function delete(User $user, MessageTemplate $template): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('message_templates.manage');
        }

        if ($user->isPromotor()) {
            return (int) $template->created_by_user_id === (int) $user->id;
        }

        return false;
    }

    public function manage(User $user, ?MessageTemplate $template = null): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('message_templates.manage');
        }

        if ($user->isPromotor()) {
            if ($template === null) {
                return true;
            }

            return $template->created_by_user_id === null || (int) $template->created_by_user_id === (int) $user->id;
        }

        return false;
    }
}
