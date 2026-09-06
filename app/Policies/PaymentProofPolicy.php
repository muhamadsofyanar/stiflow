<?php

namespace App\Policies;

use App\Models\PaymentProof;
use App\Models\User;

class PaymentProofPolicy
{
    public function download(User $user, PaymentProof $proof): bool
    {
        if ($user->isStaffOrAbove()) {
            return true;
        }

        $order = $proof->paymentAttempt?->order;

        return $order && (int) $user->id === (int) $order->user_id;
    }

    public function view(User $user, PaymentProof $proof): bool
    {
        if ($user->isStaffOrAbove()) {
            return true;
        }

        $order = $proof->paymentAttempt?->order;

        return $order && (int) $user->id === (int) $order->user_id;
    }

    public function review(User $user): bool
    {
        return $user->isStaffOrAbove();
    }
}
