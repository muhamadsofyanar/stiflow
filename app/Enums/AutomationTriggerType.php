<?php

namespace App\Enums;

enum AutomationTriggerType: string
{
    case OrderPaid = 'OrderPaid';
    case LeadCreated = 'LeadCreated';
    case ContactStageChanged = 'ContactStageChanged';
    case CampaignScheduled = 'CampaignScheduled';
    case TagAdded = 'TagAdded';
    case TagRemoved = 'TagRemoved';
    case FormSubmitted = 'FormSubmitted';
    case Manual = 'Manual';
    case ApiCall = 'ApiCall';
    case Scheduled = 'Scheduled';
}
