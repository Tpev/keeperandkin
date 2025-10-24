<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TrainingFlag extends Model
{
    public const AUDIENCE_DOG    = 'dog';
    public const AUDIENCE_PEOPLE = 'people';

    public const AUDIENCE_VALUES = [
        self::AUDIENCE_DOG,
        self::AUDIENCE_PEOPLE,
    ];

    protected $fillable = [
        'team_id',
        'name',
        'slug',
        'description',
        'is_active',
        'audience',     // NEW
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'audience'  => 'string', // NEW
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

    /** Scope helper for audience filtering */
    public function scopeForAudience($query, ?string $audience)
    {
        if ($audience && in_array($audience, self::AUDIENCE_VALUES, true)) {
            $query->where('audience', $audience);
        }
        return $query;
    }
}
