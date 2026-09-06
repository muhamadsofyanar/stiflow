<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandingPageVisit extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'landing_page_id',
        'ip_address',
        'user_agent',
        'referer',
        'visited_at',
        'contact_id',
        'cookie_uuid',
    ];

    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
            'cookie_uuid' => 'string',
        ];
    }

    public function landingPage(): BelongsTo
    {
        return $this->belongsTo(LandingPage::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
