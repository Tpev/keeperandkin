<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationResponse extends Model
{
    protected $fillable = [
        'evaluation_id','form_id','question_id','answer_option_id','answer_text','answer_value','answer_json',
    ];

    protected $casts = [
        'answer_json' => 'array',
        'answer_value'=> 'decimal:2',
    ];

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(EvaluationForm::class, 'form_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function answerOption(): BelongsTo
    {
        return $this->belongsTo(AnswerOption::class);
    }

    public function responseOptions(): HasMany
    {
        return $this->hasMany(EvaluationResponseOption::class, 'response_id');
    }
}
