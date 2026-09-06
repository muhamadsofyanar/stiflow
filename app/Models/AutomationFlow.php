<?php

namespace App\Models;

use App\Enums\AutomationFlowStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutomationFlow extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'trigger_type',
        'trigger_config_json',
        'status',
        'description',
        'total_entered_count',
        'last_triggered_at',
        'logging_enabled',
        'created_by_user_id',
    ];

    protected $casts = [
        'status' => AutomationFlowStatus::class,
        'trigger_config_json' => 'json',
        'last_triggered_at' => 'datetime',
        'logging_enabled' => 'boolean',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function steps()
    {
        return $this->hasMany(AutomationStep::class);
    }

    public function runs()
    {
        return $this->hasMany(AutomationRun::class);
    }
}
