<?php

namespace App\Models;

use App\Enums\CampaignRecipientStatus;
use App\Enums\MessageChannel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignRecipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'contact_id',
        'user_id',
        'destination',
        'channel',
        'status',
        'personalized_content_text',
        'queued_at',
        'sent_at',
        'delivered_at',
        'failed_at',
        'read_at',
        'clicked_at',
        'replied_at',
        'opted_out_at',
        'external_delivery_id',
        'last_error_message',
        'job_id',
    ];

    protected $casts = [
        'channel' => MessageChannel::class,
        'status' => CampaignRecipientStatus::class,
        'queued_at' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'failed_at' => 'datetime',
        'read_at' => 'datetime',
        'clicked_at' => 'datetime',
        'replied_at' => 'datetime',
        'opted_out_at' => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messageDeliveries()
    {
        return $this->hasMany(MessageDelivery::class);
    }
}
