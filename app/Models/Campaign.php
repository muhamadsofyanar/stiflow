<?php

namespace App\Models;

use App\Enums\CampaignStatus;
use App\Enums\MessageChannel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'channel',
        'status',
        'sender_integration_connection_id',
        'message_template_id',
        'template_snapshot_body',
        'template_snapshot_subject',
        'placeholders_values_json',
        'audience_type',
        'segment_id',
        'contact_list_id',
        'projected_audience_count',
        'schedule_send_at',
        'sending_started_at',
        'completed_at',
        'total_recipients',
        'sent_count',
        'failed_count',
        'launched_by_user_id',
        'cancel_reason',
        'parent_campaign_id',
    ];

    protected $casts = [
        'channel' => MessageChannel::class,
        'status' => CampaignStatus::class,
        'placeholders_values_json' => 'json',
        'schedule_send_at' => 'datetime',
        'sending_started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function senderIntegrationConnection()
    {
        return $this->belongsTo(IntegrationConnection::class, 'sender_integration_connection_id');
    }

    public function messageTemplate()
    {
        return $this->belongsTo(MessageTemplate::class);
    }

    public function segment()
    {
        return $this->belongsTo(Segment::class);
    }

    public function contactList()
    {
        return $this->belongsTo(ContactList::class);
    }

    public function launchedBy()
    {
        return $this->belongsTo(User::class, 'launched_by_user_id');
    }

    public function parentCampaign()
    {
        return $this->belongsTo(Campaign::class, 'parent_campaign_id');
    }

    public function childCampaigns()
    {
        return $this->hasMany(Campaign::class, 'parent_campaign_id');
    }

    public function recipients()
    {
        return $this->hasMany(CampaignRecipient::class);
    }
}
