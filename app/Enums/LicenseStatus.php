<?php

namespace App\Enums;

enum LicenseStatus: string
{
    case Active = 'Active';
    case GracePeriod = 'GracePeriod';
    case Expired = 'Expired';
    case Suspended = 'Suspended';
    case Revoked = 'Revoked';
}
