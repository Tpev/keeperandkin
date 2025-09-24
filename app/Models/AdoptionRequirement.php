<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdoptionRequirement extends Model
{
    protected $fillable = [
        'dog_id',
        'label',
        'position',
        'completed_at',
        'completed_by',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function dog(): BelongsTo
    {
        return $this->belongsTo(Dog::class);
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'completed_by');
    }

    public function getIsCompletedAttribute(): bool
    {
        return !is_null($this->completed_at);
    }
}
