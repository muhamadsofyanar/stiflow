<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutomationStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'automation_flow_id',
        'position',
        'step_type',
        'config_json',
        'label',
        'delay_seconds',
    ];

    protected $casts = [
        'config_json' => 'json',
    ];

    public function automationFlow()
    {
        return $this->belongsTo(AutomationFlow::class);
    }

    public function automationRunsAsCurrent()
    {
        return $this->hasMany(AutomationRun::class, 'current_step_id');
    }
}
