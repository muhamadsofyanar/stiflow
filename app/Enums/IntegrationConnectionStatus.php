<?php

namespace App\Enums;

enum IntegrationConnectionStatus: string
{
    case Configured = 'configured';
    case Healthy = 'healthy';
    case Degraded = 'degraded';
    case Offline = 'offline';
    case Disabled = 'disabled';
    case Error = 'error';
}
