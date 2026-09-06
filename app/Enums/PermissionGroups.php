<?php

namespace App\Enums;

enum PermissionGroups: string
{
    case Admin = 'admin';
    case Promotor = 'promotor';
    case Orders = 'orders';
    case Payments = 'payments';
    case Vouchers = 'vouchers';
    case Crm = 'crm';
    case Catalog = 'catalog';
    case Courses = 'courses';
    case Affiliate = 'affiliate';
    case Payouts = 'payouts';
    case Campaigns = 'campaigns';
    case Integrations = 'integrations';
    case Settings = 'settings';
    case Audit = 'audit';
    case Member = 'member';
}
