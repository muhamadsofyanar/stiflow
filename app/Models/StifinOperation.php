<?php

namespace App\Models;

use App\Enums\StifinOperationOutcome;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StifinOperation extends Model
{
    protected $fillable = [
        'operation_type',
        'request_reference',
        'request_hash',
        'redacted_payload_json',
        'response_body_text',
        'http_status_code',
        'outcome',
        'branch_code_snapshot',
        'promoter_code_snapshot',
        'actor_user_id',
        'fulfillment_id',
        'initiated_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'redacted_payload_json' => 'array',
            'http_status_code' => 'integer',
            'outcome' => StifinOperationOutcome::class,
            'initiated_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function fulfillment(): BelongsTo
    {
        return $this->belongsTo(Fulfillment::class);
    }

    public function isSuccess(): bool
    {
        return $this->outcome === StifinOperationOutcome::Success;
    }

    public function isAmbiguous(): bool
    {
        return in_array($this->outcome, [
            StifinOperationOutcome::AmbiguousTimeout,
            StifinOperationOutcome::AmbiguousTransport,
            StifinOperationOutcome::Unparseable,
        ], true);
    }
}
