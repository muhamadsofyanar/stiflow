<?php

namespace App\Services\Audit;

use App\Enums\AuditAction;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuditService
{
    public static function record(
        AuditAction|string $action,
        ?object $subject = null,
        mixed $before = null,
        mixed $after = null,
        ?string $correlationId = null,
        ?array $metadata = null,
        ?User $actor = null,
    ): AuditLog {
        $actor ??= (Auth::check() ? Auth::user() : null);

        $log = new AuditLog();
        $log->actor_user_id = $actor?->id;
        $log->action = is_string($action) ? $action : $action->value;
        $log->subject_type = $subject ? $subject::class : null;
        $log->subject_id = $subject?->getKey();
        $log->before_json = $before !== null ? (is_array($before) ? $before : ['value' => $before]) : null;
        $log->after_json = $after !== null ? (is_array($after) ? $after : ['value' => $after]) : null;
        $log->ip_address = request()?->ip();
        $log->user_agent = request()?->userAgent();
        $log->correlation_id = $correlationId ?? (string) Str::uuid();
        $log->metadata_json = $metadata;
        $log->created_at = now();
        $log->save();

        return $log;
    }
}
