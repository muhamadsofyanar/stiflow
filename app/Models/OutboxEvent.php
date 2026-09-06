<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutboxEvent extends Model
{
    protected $fillable = [
        'event_type',
        'idempotency_key',
        'aggregate_type',
        'aggregate_id',
        'payload_json',
        'correlation_id',
        'available_at',
        'processed_at',
        'dispatched_at',
        'job_uuid',
        'worker_result',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'payload_json' => 'array',
            'available_at' => 'datetime',
            'processed_at' => 'datetime',
            'dispatched_at' => 'datetime',
        ];
    }

    public function scopePending($query)
    {
        return $query->whereNull('processed_at');
    }
}
