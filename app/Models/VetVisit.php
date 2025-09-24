<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class VetVisit extends Model
{
    protected $fillable = [
        'dog_id',
        'visit_date',
        'reason',
        'outcome',
        'weight',
        'document_path', // NEW
    ];

    protected $casts = [
        'visit_date' => 'date',
    ];

    public function dog()
    {
        return $this->belongsTo(Dog::class);
    }

    /** Public URL for the uploaded document (if any) */
    public function getDocumentUrlAttribute(): ?string
    {
        return $this->document_path
            ? Storage::disk('public')->url($this->document_path)
            : null;
    }

    /** Delete underlying file when the record is deleted */
    protected static function booted(): void
    {
        static::deleting(function (VetVisit $visit) {
            if ($visit->document_path && Storage::disk('public')->exists($visit->document_path)) {
                Storage::disk('public')->delete($visit->document_path);
            }
        });
    }
}
