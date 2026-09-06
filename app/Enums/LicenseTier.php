<?php

namespace App\Enums;

enum LicenseTier: string
{
    case Starter = 'Starter';
    case Pro = 'Pro';
    case Enterprise = 'Enterprise';
}
