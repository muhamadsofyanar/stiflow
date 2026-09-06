<?php

namespace App\Models;

use App\Enums\MessageChannel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'channel',
        'template_type',
        'subject_line',
        'content_body',
        'language_code',
        'placeholders_json',
        'has_approved_external_template',
        'external_template_provider_id',
        'created_by_user_id',
        'is_active',
    ];

    protected $casts = [
        'channel' => MessageChannel::class,
        'placeholders_json' => 'json',
        'has_approved_external_template' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }
}
