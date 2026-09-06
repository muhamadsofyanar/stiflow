<?php

namespace App\Enums;

enum ProductLicenseKeyStatus: string
{
    case Available = 'available';
    case Assigned = 'assigned';
    case Active = 'active';
    case Suspended = 'suspended';
    case Revoked = 'revoked';
    case Expired = 'expired';
}
