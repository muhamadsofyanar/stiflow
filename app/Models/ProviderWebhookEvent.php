<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderWebhookEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_type',
        'event_type',
        'event_id',
        'occurred_at_provider',
        'signature_valid',
        'integration_connection_id',
        'headers_json',
        'payload_json',
        'raw_payload',
        'request_ip',
        'outcome',
        'outcome_message',
        'processed_job_id',
        'processed_at',
    ];

    protected $casts = [
        'occurred_at_provider' => 'datetime',
        'headers_json' => 'json',
        'payload_json' => 'json',
        'processed_at' => 'datetime',
    ];
}
