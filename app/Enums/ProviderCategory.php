<?php

namespace App\Enums;

enum ProviderCategory: string
{
    case WhatsApp = 'whatsapp';
    case Email = 'email';
    case Sms = 'sms';
    case StifinApi = 'stifin_api';
    case Payment = 'payment';
    case License = 'license';
    case Storage = 'storage';
    case WebPush = 'webpush';
}
