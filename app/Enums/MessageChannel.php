<?php

namespace App\Enums;

enum MessageChannel: string
{
    case Email = 'email';
    case WhatsApp = 'whatsapp';
    case Sms = 'sms';
    case Telegram = 'telegram';
    case Internal = 'internal';
}
