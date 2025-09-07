<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CareNote extends Model
{
    protected $fillable = [
        'dog_id', 'user_id', 'author_name', 'body', 'pinned_at',
    ];

    protected $casts = [
        'pinned_at' => 'datetime',
    ];

    public function dog()  { return $this->belongsTo(\App\Models\Dog::class); }
    public function user() { return $this->belongsTo(\App\Models\User::class); }

    /** Scopes */
    public function scopeForDog(Builder $q, int $dogId): Builder
    {
        return $q->where('dog_id', $dogId);
    }

    public function scopePinned(Builder $q): Builder
    {
        return $q->whereNotNull('pinned_at');
    }

    public function scopeRecent(Builder $q): Builder
    {
        return $q->orderByDesc('created_at');
    }
}
