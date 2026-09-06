<?php

namespace App\Models;

use App\Enums\PromoterVerificationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromoterProfile extends Model
{
    protected $fillable = [
        'user_id',
        'stifin_code',
        'sponsor_promoter_id',
        'verification_status',
        'verified_at',
        'verified_by_user_id',
        'verification_notes',
        'referral_slug',
        'notes',
        'default_commission_plan_name',
    ];

    protected function casts(): array
    {
        return [
            'verification_status' => PromoterVerificationStatus::class,
            'verified_at' => 'datetime',
            'default_commission_plan_name' => 'string',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(self::class, 'sponsor_promoter_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function downlines(): HasMany
    {
        return $this->hasMany(self::class, 'sponsor_promoter_id');
    }

    public function voucherFulfillments(): HasMany
    {
        return $this->hasMany(VoucherFulfillment::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'owner_promoter_profile_id');
    }

    public function commissionEntries(): HasMany
    {
        return $this->hasMany(CommissionEntry::class, 'beneficiary_promoter_profile_id');
    }

    public function hasCycleUpToRoot(?int $targetId = null): bool
    {
        $visited = [];
        $current = $this;

        while ($current !== null) {
            if ($targetId !== null && $current->id === $targetId) {
                return true;
            }

            if (in_array($current->id, $visited, true)) {
                return true;
            }

            $visited[] = $current->id;
            $current = $current->sponsor;
        }

        return false;
    }

    public function isVerified(): bool
    {
        return $this->verification_status === PromoterVerificationStatus::Verified;
    }

    public function scopeVerified($query)
    {
        return $query->where('verification_status', PromoterVerificationStatus::Verified);
    }

    public function scopePending($query)
    {
        return $query->where('verification_status', PromoterVerificationStatus::Pending);
    }
}
