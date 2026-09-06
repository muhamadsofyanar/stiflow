<?php

namespace App\Enums;

enum EnrollmentSource: string
{
    case Order = 'order';
    case AdminGranted = 'admin_granted';
    case PromoterGranted = 'promoter_granted';
    case Campaign = 'campaign';
    case FreeEnroll = 'free_enroll';
    case ManualImport = 'manual_import';
}
