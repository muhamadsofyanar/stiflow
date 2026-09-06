<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Contact;
use App\Models\User;

class ContactPolicy
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
            return $user->hasPermission('contacts.view_all');
        }

        return $user->isPromotor();
    }

    public function view(User $user, Contact $contact): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('contacts.view_all');
        }

        if ($user->isPromotor()) {
            $pid = $user->promoterProfile?->id;

            return $pid !== null && (int) $contact->owner_promoter_profile_id === (int) $pid;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isPromotor() || ($user->isStaff() && $user->hasPermission('contacts.create'));
    }

    public function update(User $user, Contact $contact): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('contacts.view_all');
        }

        if ($user->isPromotor()) {
            $pid = $user->promoterProfile?->id;

            return $pid !== null && (int) $contact->owner_promoter_profile_id === (int) $pid;
        }

        return false;
    }

    public function delete(User $user, Contact $contact): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('contacts.view_all');
        }

        if ($user->isPromotor()) {
            $pid = $user->promoterProfile?->id;

            return $pid !== null && (int) $contact->owner_promoter_profile_id === (int) $pid;
        }

        return false;
    }

    public function manage(User $user, ?Contact $contact = null): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('contacts.view_all');
        }

        if ($user->isPromotor()) {
            if ($contact === null) {
                return true;
            }
            $pid = $user->promoterProfile?->id;

            return $pid !== null && (int) $contact->owner_promoter_profile_id === (int) $pid;
        }

        return false;
    }
}
