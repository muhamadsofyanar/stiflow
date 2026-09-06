<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'position',
        'question_text',
        'question_type',
        'options_json',
        'correct_answer_json',
        'points',
        'explanation',
    ];

    protected $casts = [
        'options_json' => 'json',
        'correct_answer_json' => 'json',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}
