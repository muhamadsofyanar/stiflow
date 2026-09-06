<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandSetting extends Model
{
    protected $fillable = [
        'logo_path',
        'favicon_path',
        'palette_json',
        'typography_json',
        'footer_text',
    ];

    protected function casts(): array
    {
        return [
            'palette_json' => 'array',
            'typography_json' => 'array',
        ];
    }
}
