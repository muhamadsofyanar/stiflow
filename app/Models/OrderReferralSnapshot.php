<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderReferralSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'direct_promoter_profile_id',
        'ancestry_promoters_json',
        'commission_plan_id',
        'snapshot_at',
    ];

    protected $casts = [
        'ancestry_promoters_json' => 'json',
        'snapshot_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function directPromoterProfile()
    {
        return $this->belongsTo(PromoterProfile::class, 'direct_promoter_profile_id');
    }

    public function commissionPlan()
    {
        return $this->belongsTo(CommissionPlan::class);
    }
}
