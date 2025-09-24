<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationResponseOption extends Model
{
    protected $fillable = [
        'response_id','answer_option_id',
    ];

    public function response(): BelongsTo
    {
        return $this->belongsTo(EvaluationResponse::class, 'response_id');
    }

    public function answerOption(): BelongsTo
    {
        return $this->belongsTo(AnswerOption::class, 'answer_option_id');
    }
}
