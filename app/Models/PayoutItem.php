<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayoutItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payout_id',
        'beneficiary_promoter_profile_id',
        'amount',
        'entries_count',
    ];

    public function payout()
    {
        return $this->belongsTo(Payout::class);
    }

    public function beneficiaryPromoterProfile()
    {
        return $this->belongsTo(PromoterProfile::class, 'beneficiary_promoter_profile_id');
    }
}
