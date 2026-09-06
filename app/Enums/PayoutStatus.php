<?php

namespace App\Enums;

enum PayoutStatus: string
{
    case Draft = 'draft';
    case Locked = 'locked';
    case Approved = 'approved';
    case Paid = 'paid';
    case Reversed = 'reversed';
    case Rejected = 'rejected';
}
