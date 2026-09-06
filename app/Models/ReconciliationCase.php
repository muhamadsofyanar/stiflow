<?php

namespace App\Models;

use App\Enums\ReconciliationResolution;
use App\Enums\ReconciliationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ReconciliationCase extends Model
{
    protected $fillable = [
        'subject_type',
        'subject_id',
        'reason',
        'evidence_json',
        'assigned_to',
        'status',
        'resolution',
        'resolution_notes',
        'resolved_by',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'evidence_json' => 'array',
            'status' => ReconciliationStatus::class,
            'resolution' => ReconciliationResolution::class,
            'resolved_at' => 'datetime',
        ];
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
