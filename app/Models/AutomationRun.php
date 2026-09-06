<?php

namespace App\Models;

use App\Enums\AutomationFlowStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutomationRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'automation_flow_id',
        'contact_id',
        'user_id',
        'order_id',
        'current_step_id',
        'current_step_position',
        'status',
        'entered_at',
        'next_step_scheduled_at',
        'completed_at',
        'failed_at',
        'failure_reason',
        'correlation_id',
        'context_snapshot_json',
    ];

    protected $casts = [
        'entered_at' => 'datetime',
        'next_step_scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
        'context_snapshot_json' => 'json',
    ];

    public function automationFlow()
    {
        return $this->belongsTo(AutomationFlow::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function currentStep()
    {
        return $this->belongsTo(AutomationStep::class, 'current_step_id');
    }
}
