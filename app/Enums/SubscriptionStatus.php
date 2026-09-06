<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Subscribed = 'subscribed';
    case PendingConfirmation = 'pending_confirmation';
    case Unsubscribed = 'unsubscribed';
    case Bounced = 'bounced';
    case Invalid = 'invalid';
}
