<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PendingPayment = 'pending_payment';
    case PaymentSubmitted = 'payment_submitted';
    case Paid = 'paid';
    case Fulfilling = 'fulfilling';
    case Completed = 'completed';
    case NeedsReview = 'needs_review';
    case Rejected = 'rejected';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PendingPayment => 'Menunggu Pembayaran',
            self::PaymentSubmitted => 'Bukti Diupload',
            self::Paid => 'Dibayar',
            self::Fulfilling => 'Diproses',
            self::Completed => 'Selesai',
            self::NeedsReview => 'Perlu Review',
            self::Rejected => 'Ditolak',
            self::Expired => 'Kedaluwarsa',
            self::Cancelled => 'Dibatalkan',
            self::Refunded => 'Dikembalikan',
        };
    }
}
