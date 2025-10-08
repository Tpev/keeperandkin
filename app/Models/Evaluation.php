<?php

// app/Models/Evaluation.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evaluation extends Model
{
    protected $fillable = [
        'dog_id','user_id','form_id',
        'answers','scores','submitted_at',
        'score','category_scores','red_flags',
    ];

    protected $casts = [
        'answers'         => 'array',
        'scores'          => 'array',
        'category_scores' => 'array',
        'red_flags'       => 'array',
        'submitted_at'    => 'datetime',
    ];

    public function responses(): HasMany
    {
        // evaluation_responses.evaluation_id → evaluations.id
        return $this->hasMany(EvaluationResponse::class);
    }
}
