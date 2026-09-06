<?php

namespace App\Models;

use App\Enums\ReferralStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    use HasFactory;

    protected $fillable = [
        'referrer_promoter_profile_id',
        'referred_contact_id',
        'referred_user_id',
        'referred_promoter_profile_id',
        'status',
        'visit_id',
        'converted_at',
        'rejection_reason',
    ];

    protected $casts = [
        'status' => ReferralStatus::class,
        'converted_at' => 'datetime',
    ];

    public function referrerPromoterProfile()
    {
        return $this->belongsTo(PromoterProfile::class, 'referrer_promoter_profile_id');
    }

    public function referredContact()
    {
        return $this->belongsTo(Contact::class, 'referred_contact_id');
    }

    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function referredPromoterProfile()
    {
        return $this->belongsTo(PromoterProfile::class, 'referred_promoter_profile_id');
    }

    public function visit()
    {
        return $this->belongsTo(ReferralVisit::class, 'visit_id');
    }
}
