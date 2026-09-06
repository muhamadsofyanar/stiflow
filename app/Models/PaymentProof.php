<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentProof extends Model
{
    protected $fillable = [
        'payment_attempt_id',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'bank_name',
        'sender_name',
        'sender_bank',
        'transfer_amount',
        'transfer_time',
        'review_note',
        'review_status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'transfer_amount' => 'decimal:2',
            'transfer_time' => 'datetime',
            'reviewed_at' => 'datetime',
            'file_size' => 'integer',
        ];
    }

    public function paymentAttempt(): BelongsTo
    {
        return $this->belongsTo(PaymentAttempt::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->review_status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->review_status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->review_status === 'rejected';
    }
}
