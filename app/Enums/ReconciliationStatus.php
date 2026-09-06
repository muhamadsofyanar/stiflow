<?php

namespace App\Enums;

enum ReconciliationStatus: string
{
    case Open = 'open';
    case Investigating = 'investigating';
    case Resolved = 'resolved';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Perlu Review (Open)',
            self::Investigating => 'Sedang Diselidiki',
            self::Resolved => 'Selesai',
            self::Archived => 'Diarsipkan',
        };
    }
}
