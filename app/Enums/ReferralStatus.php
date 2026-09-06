<?php

namespace App\Enums;

enum ReferralStatus: string
{
    case Pending = 'pending';
    case Lead = 'lead';
    case Converted = 'converted';
    case Promoted = 'promoted';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
}
