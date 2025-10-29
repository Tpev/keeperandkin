<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dog extends Model
{
    use HasFactory;

    protected $fillable = [
        'serial_number',
        'team_id',
        'name',
        'breed',
        'age',
        'sex',
        'description',
        'photo_path',
        'location',
        'approx_dob',
        'fixed',
        'color',
        'size',
        'microchip',
        'heartworm',
        'fiv_l',
        'flv',
        'housetrained',
        'good_with_dogs',
        'good_with_cats',
        'good_with_children',
    ];

    protected $casts = [
        'approx_dob' => 'date',
        'fixed'      => 'boolean',
    ];

    /* Relationships */
    public function team()
    {
        // IMPORTANT: reference your concrete App\Models\Team, not Laravel\Jetstream\Team
        return $this->belongsTo(\App\Models\Team::class);
    }

    public function evaluations()
    {
        return $this->hasMany(\App\Models\Evaluation::class);
    }

    public function latestEvaluation()
    {
        return $this->hasOne(\App\Models\Evaluation::class)->latestOfMany();
    }

    public function vetMetric()
    {
        return $this->hasOne(\App\Models\VetMetric::class);
    }

    public function vetVisits()
    {
        return $this->hasMany(\App\Models\VetVisit::class)->orderByDesc('visit_date');
    }

    public function careNotes()
    {
        return $this->hasMany(\App\Models\CareNote::class)->orderByDesc('created_at');
    }

    public function dietProfile()
    {
        return $this->hasOne(\App\Models\DietProfile::class);
    }

    public function dietEntries()
    {
        return $this->hasMany(\App\Models\DietEntry::class)->orderByDesc('fed_at');
    }

    public function getApproxDobUsAttribute(): ?string
    {
        return $this->approx_dob?->format('m/d/Y');
    }

    public function setApproxDobAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['approx_dob'] = null;
            return;
        }

        $dt = \DateTime::createFromFormat('m/d/Y', $value)
            ?: \DateTime::createFromFormat('Y-m-d', $value);

        $this->attributes['approx_dob'] = $dt ? $dt->format('Y-m-d') : $value;
    }

    public function transfer()
    {
        return $this->hasMany(\App\Models\DogTransfer::class)->latest();
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if ($this->photo_path) {
            return asset('storage/' . ltrim($this->photo_path, '/'));
        }

        if (array_key_exists('photo', $this->attributes) && $this->attributes['photo']) {
            return asset('storage/' . ltrim($this->attributes['photo'], '/'));
        }

        return null;
    }
}
