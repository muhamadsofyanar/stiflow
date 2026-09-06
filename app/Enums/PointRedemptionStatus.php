<?php

namespace App\Enums;

enum PointRedemptionStatus: string
{
    case Pending = 'pending';
    case Applied = 'applied';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Rejected = 'rejected';
}
