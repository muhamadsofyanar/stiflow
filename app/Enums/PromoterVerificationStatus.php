<?php

namespace App\Enums;

enum PromoterVerificationStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Verified => 'Terverifikasi',
            self::Rejected => 'Ditolak',
        };
    }
}
