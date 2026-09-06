<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'title',
        'description',
        'priority',
        'status',
        'due_at',
        'remind_at',
        'assignee_user_id',
        'created_by_user_id',
        'related_deal_id',
    ];

    protected $casts = [
        'priority' => TaskPriority::class,
        'status' => TaskStatus::class,
        'due_at' => 'datetime',
        'remind_at' => 'datetime',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_user_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function relatedDeal()
    {
        return $this->belongsTo(Contact::class, 'related_deal_id');
    }
}
