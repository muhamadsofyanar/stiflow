<?php

namespace App\Enums;

enum PixelProvider: string
{
    case MetaPixel = 'MetaPixel';
    case GoogleAnalytics4 = 'GoogleAnalytics4';
    case TikTokPixel = 'TikTokPixel';
    case LinkedInInsight = 'LinkedInInsight';
}
