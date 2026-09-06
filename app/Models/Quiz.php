<?php

namespace App\Models;

use App\Enums\QuizType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'passing_percent',
        'max_attempts',
        'time_limit_minutes',
        'shuffle_questions',
        'show_correct_answer_after_submit',
    ];

    protected $casts = [
        'shuffle_questions' => 'boolean',
        'show_correct_answer_after_submit' => 'boolean',
    ];

    public function questions()
    {
        return $this->hasMany(QuizQuestion::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
