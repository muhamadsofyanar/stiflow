<?php

namespace App\Enums;

enum ReconciliationResolution: string
{
    case ManualSuccess = 'manual_success';
    case ManualRollback = 'manual_rollback';
    case NoAction = 'no_action';
    case ConfirmedSuccess = 'confirmed_success';
    case RetryApplied = 'retry_applied';
    case CancelledCompensated = 'cancelled_compensated';
    case RejectedAsDuplicate = 'rejected_as_duplicate';

    public function label(): string
    {
        return match ($this) {
            self::ManualSuccess => 'Manual Sukses (Voucher Terkirim)',
            self::ManualRollback => 'Manual Rollback (Refund / Reject)',
            self::NoAction => 'Tidak Ada Aksi',
            self::ConfirmedSuccess => 'Dikonfirmasi Sukses',
            self::RetryApplied => 'Retry Dijalankan',
            self::CancelledCompensated => 'Dibatalkan + Kompensasi',
            self::RejectedAsDuplicate => 'Ditolak: Duplikat',
        };
    }
}
