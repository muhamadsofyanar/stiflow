<?php

namespace App\Models;

use App\Enums\EnrollmentSource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'enrollment_source',
        'order_id',
        'granted_by_user_id',
        'promoter_profile_id_granted',
        'is_active',
        'expires_at',
        'completed_at',
        'progress_percent',
        'last_accessed_at',
    ];

    protected $casts = [
        'enrollment_source' => EnrollmentSource::class,
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_accessed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function grantedBy()
    {
        return $this->belongsTo(User::class, 'granted_by_user_id');
    }

    public function promoterProfileGranted()
    {
        return $this->belongsTo(PromoterProfile::class, 'promoter_profile_id_granted');
    }

    public function lessonProgress()
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function downloadGrants()
    {
        return $this->hasMany(DownloadGrant::class);
    }
}
