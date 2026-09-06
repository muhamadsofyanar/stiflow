<?php

namespace App\Models;

use App\Enums\CustomFieldType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomField extends Model
{
    use HasFactory;

    protected $fillable = [
        'model_type',
        'name',
        'key',
        'field_type',
        'is_required',
        'options',
        'validation_rules',
        'position',
    ];

    protected $casts = [
        'field_type' => CustomFieldType::class,
        'is_required' => 'boolean',
        'options' => 'json',
        'validation_rules' => 'json',
    ];

    public function contactCustomValues()
    {
        return $this->hasMany(ContactCustomValue::class);
    }
}
