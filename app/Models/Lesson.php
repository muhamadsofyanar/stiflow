<?php

namespace App\Models;

use App\Enums\LessonType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_module_id',
        'position',
        'title',
        'lesson_type',
        'content_text',
        'video_url',
        'video_provider',
        'duration_minutes',
        'is_preview_allowed',
        'is_published',
        'digital_asset_id',
        'quiz_id',
        'require_quiz_pass',
        'min_quiz_score_percent',
        'custom_fields_json',
    ];

    protected $casts = [
        'lesson_type' => LessonType::class,
        'is_preview_allowed' => 'boolean',
        'is_published' => 'boolean',
        'require_quiz_pass' => 'boolean',
        'custom_fields_json' => 'json',
    ];

    public function courseModule()
    {
        return $this->belongsTo(CourseModule::class);
    }

    public function digitalAsset()
    {
        return $this->belongsTo(DigitalAsset::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function lessonProgress()
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
