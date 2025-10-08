<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingSession extends Model
{
    protected $fillable = [
        'team_id','name','slug','description','video_url','pdf_path','duration_minutes','is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'duration_minutes' => 'integer',
    ];

    public function flags(): BelongsToMany
    {
        return $this->belongsToMany(TrainingFlag::class)
            ->withTimestamps()
            ->withPivot('position');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(DogTrainingAssignment::class);
    }

    public function getPdfUrlAttribute(): ?string
    {
        return $this->pdf_path ? asset('storage/'.$this->pdf_path) : null;
    }
}
