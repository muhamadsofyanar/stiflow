<?php

namespace App\Models;

use App\Enums\PointRedemptionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointRedemptionOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reward_product_id',
        'points_used',
        'discount_applied_money',
        'status',
        'applied_to_order_id',
        'point_ledger_entry_id',
        'rejection_reason',
    ];

    protected $casts = [
        'status' => PointRedemptionStatus::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rewardProduct()
    {
        return $this->belongsTo(Product::class, 'reward_product_id');
    }

    public function appliedToOrder()
    {
        return $this->belongsTo(Order::class, 'applied_to_order_id');
    }

    public function pointLedgerEntry()
    {
        return $this->belongsTo(PointLedgerEntry::class);
    }
}
