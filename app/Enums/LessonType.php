<?php

namespace App\Enums;

enum LessonType: string
{
    case Text = 'text';
    case Video = 'video';
    case Audio = 'audio';
    case File = 'file';
    case Quiz = 'quiz';
    case Live = 'live';
}
