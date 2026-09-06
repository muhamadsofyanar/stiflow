<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'referral_link_id',
        'promoter_profile_id',
        'ip_hash',
        'user_agent',
        'landing_path',
        'referrer_host',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'fingerprint',
        'converted_at',
        'converted_to_contact_id',
    ];

    protected $casts = [
        'converted_at' => 'datetime',
    ];

    public function referralLink()
    {
        return $this->belongsTo(ReferralLink::class);
    }

    public function promoterProfile()
    {
        return $this->belongsTo(PromoterProfile::class);
    }

    public function convertedToContact()
    {
        return $this->belongsTo(Contact::class, 'converted_to_contact_id');
    }

    public function referral()
    {
        return $this->hasOne(Referral::class, 'visit_id');
    }
}
