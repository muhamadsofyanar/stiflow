<?php

namespace App\Enums;

enum ProductType: string
{
    case Voucher = 'voucher';
    case Course = 'course';
    case Digital = 'digital';
    case Service = 'service';
    case Membership = 'membership';
}

enum ProductStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Draft = 'draft';
}

enum FulfillmentType: string
{
    case Voucher = 'voucher';
    case Enrollment = 'enrollment';
    case Digital = 'digital';
    case Manual = 'manual';
    case License = 'license';
}
