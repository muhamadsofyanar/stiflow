<?php

namespace App\Enums;

enum UpdateChannel: string
{
    case Stable = 'Stable';
    case Preview = 'Preview';
}
