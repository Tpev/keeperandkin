<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationFormQuestion extends Model
{
    protected $fillable = [
        'form_id','section_id','question_id','position','required','visibility','meta',
    ];

    protected $casts = [
        'required'  => 'bool',
        'meta'      => 'array',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(EvaluationForm::class, 'form_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(EvaluationSection::class, 'section_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
	public function followUpRule() // this FQ is the CHILD if present
{
    return $this->hasOne(\App\Models\EvaluationFollowUp::class, 'child_form_question_id');
}

	public function childFollowUps() // this FQ is the PARENT of zero or more children
	{
		return $this->hasMany(\App\Models\EvaluationFollowUp::class, 'parent_form_question_id');
	}

}
