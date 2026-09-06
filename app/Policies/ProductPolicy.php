<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Product $product): bool
    {
        if ($product->visibility === 'public') {
            return true;
        }
        if ($product->visibility === 'login_only') {
            return (bool) $user;
        }
        if ($product->visibility === 'promotor_only') {
            return (bool) $user && $user->isPromotor();
        }

        return $user->isStaffOrAbove();
    }

    public function manage(User $user): bool
    {
        return $user->isStaffOrAbove();
    }
}
