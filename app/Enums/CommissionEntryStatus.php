<?php

namespace App\Enums;

enum CommissionEntryStatus: string
{
    case Pending = 'pending';
    case Payable = 'payable';
    case Locked = 'locked';
    case Paid = 'paid';
    case Reversed = 'reversed';
    case Rejected = 'rejected';
}
