<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Segment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'filter_rules_json',
        'cached_count',
        'cached_at',
        'created_by_user_id',
    ];

    protected $casts = [
        'filter_rules_json' => 'json',
        'cached_at' => 'datetime',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }
}
