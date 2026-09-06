<?php

namespace App\Models;

use App\Enums\PointDirection;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'last_login_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isPromotor(): bool
    {
        return $this->role === UserRole::Promotor;
    }

    public function isStaff(): bool
    {
        return $this->role === UserRole::Staff;
    }

    public function isStaffOrAbove(): bool
    {
        return in_array($this->role, [UserRole::Admin, UserRole::Staff], true);
    }

    public function scopePromotor($query)
    {
        return $query->where('role', UserRole::Promotor);
    }

    public function scopeAdmin($query)
    {
        return $query->where('role', UserRole::Admin);
    }

    public function scopeStaff($query)
    {
        return $query->where('role', UserRole::Staff);
    }

    public function scopeVerified($query)
    {
        return $query->whereHas('promoterProfile', function ($q) {
            $q->where('verification_status', \App\Enums\PromoterVerificationStatus::Verified);
        });
    }

    public function promoterProfile(): HasOne
    {
        return $this->hasOne(PromoterProfile::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'actor_user_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user', 'user_id', 'permission_id');
    }

    public function hasPermission(string|array $keys): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $keysToCheck = is_array($keys) ? $keys : [$keys];

        return $this->permissions()->whereIn('key', $keysToCheck)->exists();
    }

    public function paymentProofs(): MorphMany
    {
        return $this->morphMany(PaymentProof::class, 'proofable');
    }

    public function pointsBalance(): int
    {
        $credit = (int) DB::table('point_ledger_entries')
            ->where('user_id', $this->id)
            ->where('direction', PointDirection::Credit->value)
            ->sum('amount_points');

        $debit = (int) DB::table('point_ledger_entries')
            ->where('user_id', $this->id)
            ->where('direction', PointDirection::Debit->value)
            ->sum('amount_points');

        return $credit - $debit;
    }
}
