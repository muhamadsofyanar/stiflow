<?php

namespace App\Enums;

enum DeliveryOutcome: string
{
    case Sent = 'sent';
    case Delivered = 'delivered';
    case Read = 'read';
    case Clicked = 'clicked';
    case FailedPermanent = 'failed_permanent';
    case FailedTemporary = 'failed_temporary';
    case SkippedUnsubscribed = 'skipped_unsubscribed';
    case SkippedInvalid = 'skipped_invalid';
    case Queued = 'queued';
}
