<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'max_levels',
        'is_active',
        'is_default',
        'starts_at',
        'ends_at',
        'minimum_commission_per_entry',
        'minimum_payout_total',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function rules()
    {
        return $this->hasMany(CommissionRule::class);
    }

    public function orderReferralSnapshots()
    {
        return $this->hasMany(OrderReferralSnapshot::class);
    }

    public function commissionEntries()
    {
        return $this->hasMany(CommissionEntry::class);
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }
}
