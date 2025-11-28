<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DogMedia extends Model
{
    protected $fillable = [
        'dog_id',
        'media_type',
        'file_path',
        'video_url',
        'caption',
        'sort_order',
    ];

    public function dog(): BelongsTo
    {
        return $this->belongsTo(Dog::class);
    }

    public function isImage(): bool
    {
        return $this->media_type === 'image';
    }

    public function isVideo(): bool
    {
        return $this->media_type === 'video';
    }

    public function url(): ?string
    {
        return $this->file_path
            ? asset('storage/'.$this->file_path)
            : $this->video_url;
    }
}
