<?php

namespace App\Exceptions;

use RuntimeException;

class ProviderNotConfigured extends RuntimeException
{
    public static function for(string $provider): self
    {
        return new self("Provider pembayaran {$provider} belum tersedia pada Voucher MVP. Gunakan transfer manual.");
    }
}
