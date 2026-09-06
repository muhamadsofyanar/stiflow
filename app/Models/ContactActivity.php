<?php

namespace App\Models;

use App\Enums\ActivityType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'type',
        'content',
        'subject',
        'user_id',
        'scheduled_at',
        'done_at',
        'outcome',
    ];

    protected $casts = [
        'type' => ActivityType::class,
        'scheduled_at' => 'datetime',
        'done_at' => 'datetime',
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
