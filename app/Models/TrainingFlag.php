<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TrainingFlag extends Model
{
    protected $fillable = ['team_id','name','slug','description','is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function sessions(): BelongsToMany
    {
        return $this->belongsToMany(TrainingSession::class)
            ->withTimestamps()
            ->withPivot('position')
            ->orderBy('training_flag_training_session.position');
    }

    public function answerOptions(): BelongsToMany
    {
        return $this->belongsToMany(AnswerOption::class)->withTimestamps();
    }
}
