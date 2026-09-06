<?php

namespace App\Enums;

enum PointDirection: string
{
    case Credit = 'in';
    case Debit = 'out';
}
