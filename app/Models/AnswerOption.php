<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnswerOption extends Model
{
    protected $fillable = [
        'question_id','label','value','position','score_map','flags',
    ];

    protected $casts = [
        'score_map' => 'array',
        'flags'     => 'array',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
