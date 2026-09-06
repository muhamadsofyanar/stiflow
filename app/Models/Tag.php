<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color_hex',
        'category',
    ];

    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'contact_tag');
    }
}
