<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaffOrAbove();
    }

    public function view(User $user, Order $order): bool
    {
        if ($user->isStaffOrAbove()) {
            return true;
        }

        return (int) $user->id === (int) $order->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isVerifiedPromotor();
    }

    public function submitProof(User $user, Order $order): bool
    {
        return (int) $user->id === (int) $order->user_id;
    }

    public function verify(User $user): bool
    {
        return $user->isStaffOrAbove();
    }

    public function manage(User $user): bool
    {
        return $user->isStaffOrAbove();
    }
}
