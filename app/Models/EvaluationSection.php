<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationSection extends Model
{
    protected $fillable = ['form_id','title','slug','position'];

    public function form(): BelongsTo
    {
        return $this->belongsTo(EvaluationForm::class, 'form_id');
    }

    public function formQuestions(): HasMany
    {
        return $this->hasMany(EvaluationFormQuestion::class, 'section_id');
    }
}
