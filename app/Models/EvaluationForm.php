<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationForm extends Model
{
    protected $fillable = [
        'team_id','name','slug','version','is_active','starts_at','ends_at','created_by','updated_by',
    ];

    protected $casts = [
        'is_active' => 'bool',
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(EvaluationSection::class, 'form_id');
    }

    public function formQuestions(): HasMany
    {
        return $this->hasMany(EvaluationFormQuestion::class, 'form_id');
    }
}
