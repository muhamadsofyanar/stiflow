<?php

namespace App\Models;

use App\Enums\CommissionEntryStatus;
use App\Enums\CommissionRuleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'order_id',
        'beneficiary_promoter_profile_id',
        'level',
        'status',
        'amount',
        'amount_paid',
        'rate_value',
        'rate_type',
        'commission_plan_id',
        'commission_rule_id',
        'reversed_from_entry_id',
        'payout_id',
        'rejection_reason',
        'earned_at',
        'locked_at',
        'paid_at',
        'reversed_at',
        'reference_id',
    ];

    protected $casts = [
        'status' => CommissionEntryStatus::class,
        'rate_type' => CommissionRuleType::class,
        'earned_at' => 'datetime',
        'locked_at' => 'datetime',
        'paid_at' => 'datetime',
        'reversed_at' => 'datetime',
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function beneficiaryPromoterProfile()
    {
        return $this->belongsTo(PromoterProfile::class, 'beneficiary_promoter_profile_id');
    }

    public function commissionPlan()
    {
        return $this->belongsTo(CommissionPlan::class);
    }

    public function commissionRule()
    {
        return $this->belongsTo(CommissionRule::class);
    }

    public function reversedFromEntry()
    {
        return $this->belongsTo(CommissionEntry::class, 'reversed_from_entry_id');
    }

    public function payout()
    {
        return $this->belongsTo(Payout::class);
    }

    public function reversalEntries()
    {
        return $this->hasMany(CommissionEntry::class, 'reversed_from_entry_id');
    }
}
