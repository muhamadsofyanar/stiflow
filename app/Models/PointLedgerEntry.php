<?php

namespace App\Models;

use App\Enums\PointDirection;
use App\Enums\PointEntryType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointLedgerEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'entry_type',
        'direction',
        'amount_points',
        'balance_after_points',
        'reason_code',
        'reason_text',
        'related_order_id',
        'related_order_item_id',
        'related_promoter_profile_id',
        'reference_id',
        'reversed_from_entry_id',
        'expires_at',
        'performed_by_user_id',
        'meta_json',
    ];

    protected $casts = [
        'entry_type' => PointEntryType::class,
        'direction' => PointDirection::class,
        'expires_at' => 'datetime',
        'meta_json' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function relatedOrder()
    {
        return $this->belongsTo(Order::class, 'related_order_id');
    }

    public function relatedOrderItem()
    {
        return $this->belongsTo(OrderItem::class, 'related_order_item_id');
    }

    public function relatedPromoterProfile()
    {
        return $this->belongsTo(PromoterProfile::class, 'related_promoter_profile_id');
    }

    public function reversedFromEntry()
    {
        return $this->belongsTo(PointLedgerEntry::class, 'reversed_from_entry_id');
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by_user_id');
    }

    public function reversalEntries()
    {
        return $this->hasMany(PointLedgerEntry::class, 'reversed_from_entry_id');
    }

    public function pointRedemptionOrders()
    {
        return $this->hasMany(PointRedemptionOrder::class);
    }
}
