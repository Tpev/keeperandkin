<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationQuestionParam extends Model
{
    protected $fillable = [
        'category_key',
        'question_key',
        'weight',
        'training_category',
        'flags',
    ];

    protected $casts = [
        'weight' => 'integer',
        'flags'  => 'array',
    ];
}
