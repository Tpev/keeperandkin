<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'slug','prompt','help_text','type','category','meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function answerOptions(): HasMany
    {
        return $this->hasMany(AnswerOption::class);
    }
}
