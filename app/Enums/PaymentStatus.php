<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Submitted = 'submitted';
    case Verified = 'verified';
    case Paid = 'paid';
    case Processed = 'processed';
    case NeedsReview = 'needs_review';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case Rejected = 'rejected';
    case Failed = 'failed';
    case Refunded = 'refunded';
}
