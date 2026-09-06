<?php

namespace App\Models;

use App\Enums\CommissionRuleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'commission_plan_id',
        'product_id',
        'product_type_filter',
        'level',
        'rule_type',
        'value',
        'cap_per_unit_max',
    ];

    protected $casts = [
        'rule_type' => CommissionRuleType::class,
    ];

    public function commissionPlan()
    {
        return $this->belongsTo(CommissionPlan::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function commissionEntries()
    {
        return $this->hasMany(CommissionEntry::class);
    }
}
