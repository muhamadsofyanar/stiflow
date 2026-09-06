<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'description',
        'cover_image_id',
        'difficulty_level',
        'estimated_minutes',
        'is_published',
        'published_at',
        'is_free',
        'linked_product_id',
        'author_user_id',
        'meta_tags',
        'stifin_result_mapping_json',
        'is_archived',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'is_free' => 'boolean',
        'meta_tags' => 'json',
        'is_archived' => 'boolean',
    ];

    public function coverImage()
    {
        return $this->belongsTo(DigitalAsset::class, 'cover_image_id');
    }

    public function linkedProduct()
    {
        return $this->belongsTo(Product::class, 'linked_product_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function modules()
    {
        return $this->hasMany(CourseModule::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function digitalAssets()
    {
        return $this->hasMany(DigitalAsset::class);
    }
}
