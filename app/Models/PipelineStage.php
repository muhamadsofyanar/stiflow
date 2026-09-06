<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PipelineStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'pipeline_id',
        'name',
        'slug',
        'position',
        'color_hex',
        'is_won_stage',
        'is_lost_stage',
    ];

    public function pipeline()
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class, 'stage_id');
    }
}
