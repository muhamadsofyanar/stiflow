<?php

namespace App\Enums;

enum CustomFieldType: string
{
    case Text = 'text';
    case Number = 'number';
    case Date = 'date';
    case DateTime = 'datetime';
    case Select = 'select';
    case Multiselect = 'multiselect';
    case Boolean = 'boolean';
    case Textarea = 'textarea';
    case Url = 'url';
    case Email = 'email';
    case Phone = 'phone';
}
