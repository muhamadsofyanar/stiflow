<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'promoter_profile_id',
        'slug',
        'name',
        'destination_path',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'is_active',
        'expires_at',
        'total_visits',
        'total_leads',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function promoterProfile()
    {
        return $this->belongsTo(PromoterProfile::class);
    }

    public function visits()
    {
        return $this->hasMany(ReferralVisit::class);
    }
}
