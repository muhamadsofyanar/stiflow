<?php

namespace App\Models;

use App\Enums\MessageChannel;
use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'user_id',
        'channel',
        'destination_hash',
        'destination_value_masked',
        'status',
        'allow_transactional',
        'allow_marketing',
        'confirmed_at',
        'unsubscribed_at',
        'unsubscribe_reason',
        'consent_source',
    ];

    protected $casts = [
        'channel' => MessageChannel::class,
        'status' => SubscriptionStatus::class,
        'allow_transactional' => 'boolean',
        'allow_marketing' => 'boolean',
        'confirmed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
