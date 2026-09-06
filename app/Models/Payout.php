<?php

namespace App\Models;

use App\Enums\PayoutStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Payout extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_number',
        'commission_plan_id',
        'status',
        'total_amount',
        'entry_count',
        'prepared_by_user_id',
        'approved_by_user_id',
        'paid_by_user_id',
        'payment_method',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'notes',
        'admin_rejection_notes',
        'locked_at',
        'approved_at',
        'paid_at',
        'reversed_at',
        'period_start_date',
        'period_end_date',
    ];

    protected $casts = [
        'status' => PayoutStatus::class,
        'locked_at' => 'datetime',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'reversed_at' => 'datetime',
        'period_start_date' => 'date',
        'period_end_date' => 'date',
    ];

    public function commissionPlan()
    {
        return $this->belongsTo(CommissionPlan::class);
    }

    public function preparedBy()
    {
        return $this->belongsTo(User::class, 'prepared_by_user_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by_user_id');
    }

    public function items()
    {
        return $this->hasMany(PayoutItem::class);
    }

    public function commissionEntries()
    {
        return $this->hasMany(CommissionEntry::class);
    }

    public function paymentProofs(): MorphMany
    {
        return $this->morphMany(PaymentProof::class, 'proofable');
    }
}
