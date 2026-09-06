<?php

namespace App\Enums;

enum CampaignRecipientStatus: string
{
    case Queued = 'queued';
    case Sent = 'sent';
    case Delivered = 'delivered';
    case Failed = 'failed';
    case Read = 'read';
    case Clicked = 'clicked';
    case Replied = 'replied';
    case OptedOut = 'opted_out';
    case SkippedUnsubscribed = 'skipped_unsubscribed';
}
