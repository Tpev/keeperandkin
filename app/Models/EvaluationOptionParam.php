<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationOptionParam extends Model
{
    protected $fillable = [
        'category_key',
        'question_key',
        'option_key',
        'weight',
        'training_category', // legacy single value (kept for now)
        'training_tags',     // NEW
        'flags',             // legacy/general flags
        'red_flags',         // NEW
    ];

    protected $casts = [
        'training_tags' => 'array',
        'flags'         => 'array',
        'red_flags'     => 'array',
    ];
}
