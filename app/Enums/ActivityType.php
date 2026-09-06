<?php

namespace App\Enums;

enum ActivityType: string
{
    case Call = 'call';
    case WhatsApp = 'whatsapp';
    case Email = 'email';
    case Meeting = 'meeting';
    case Note = 'note';
    case Sms = 'sms';
    case Other = 'other';
}
