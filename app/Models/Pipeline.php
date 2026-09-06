<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pipeline extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_default',
        'owner_user_id',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function stages()
    {
        return $this->hasMany(PipelineStage::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }
}
