<?php

namespace App\Enums;

enum FollowUpStatus: string
{
    case Scheduled = 'scheduled';
    case Queued = 'queued';
    case Sending = 'sending';
    case Sent = 'sent';
    case Delivered = 'delivered';
    case Opened = 'opened';
    case Clicked = 'clicked';
    case Replied = 'replied';
    case Bounced = 'bounced';
    case Complaint = 'complaint';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Skipped = 'skipped';
}
