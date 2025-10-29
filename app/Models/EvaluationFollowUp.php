<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationFollowUp extends Model
{
    protected $fillable = [
        'child_form_question_id',
        'parent_form_question_id',
        'trigger_option_ids',
        'display_mode',
        'required_mode',
    ];

    protected $casts = [
        'trigger_option_ids' => 'array',   // ← IMPORTANT
    ];

    public function child()
    {
        return $this->belongsTo(EvaluationFormQuestion::class, 'child_form_question_id');
    }

    public function parent()
    {
        return $this->belongsTo(EvaluationFormQuestion::class, 'parent_form_question_id');
    }
}
