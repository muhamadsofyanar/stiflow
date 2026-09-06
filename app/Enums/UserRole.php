<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Staff = 'staff';
    case Promotor = 'promotor';
    case Member = 'member';

    public function isAdmin(): bool
    {
        return $this === self::Admin;
    }

    public function isPromotor(): bool
    {
        return $this === self::Promotor;
    }

    public function isStaffOrAbove(): bool
    {
        return in_array($this, [self::Admin, self::Staff], true);
    }
}
