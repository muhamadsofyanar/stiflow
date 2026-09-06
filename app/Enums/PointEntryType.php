<?php

namespace App\Enums;

enum PointEntryType: string
{
    case Earn = 'earn';
    case Redeem = 'redeem';
    case Expire = 'expire';
    case Adjustment = 'adjustment';
    case Reversal = 'reversal';
}
