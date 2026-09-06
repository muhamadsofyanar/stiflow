<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BranchSetting extends Model
{
    protected $fillable = [
        'branch_code',
        'brand_name',
        'contact',
        'address',
        'bank_name',
        'bank_account',
        'bank_account_name',
        'locale',
        'timezone',
        'currency',
    ];

    public function brandSetting(): HasOne
    {
        return $this->hasOne(BrandSetting::class);
    }

    public static function current(): self
    {
        return self::query()->firstOrFail();
    }
}
