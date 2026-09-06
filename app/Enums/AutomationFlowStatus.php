<?php

namespace App\Enums;

enum AutomationFlowStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Paused = 'paused';
    case Archived = 'archived';
}
