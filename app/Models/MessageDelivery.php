<?php

namespace App\Models;

use App\Enums\DeliveryOutcome;
use App\Enums\MessageChannel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_type',
        'channel',
        'campaign_recipient_id',
        'contact_id',
        'user_id',
        'destination',
        'subject',
        'body_text',
        'provider_type',
        'integration_connection_id',
        'outcome',
        'external_id',
        'error_message',
        'provider_response_json',
        'queued_at',
        'sent_at',
        'delivered_at',
        'read_at',
        'clicked_at',
        'failed_at',
        'correlation_id',
        'created_by_user_id',
        'idempotency_key',
    ];

    protected $casts = [
        'channel' => MessageChannel::class,
        'outcome' => DeliveryOutcome::class,
        'provider_response_json' => 'json',
        'queued_at' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'clicked_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function campaignRecipient()
    {
        return $this->belongsTo(CampaignRecipient::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function integrationConnection()
    {
        return $this->belongsTo(IntegrationConnection::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
