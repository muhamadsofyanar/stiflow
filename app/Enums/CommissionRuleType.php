<?php

namespace App\Enums;

enum CommissionRuleType: string
{
    case Percentage = 'percent';
    case FixedAmount = 'fixed';
}
