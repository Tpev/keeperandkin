<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Jetstream\Team;     // Jetstream's built-in team model

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
    ];

    /* Relationships */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }
public function evaluations() { return $this->hasMany(\App\Models\Evaluation::class); }
public function latestEvaluation() { return $this->hasOne(\App\Models\Evaluation::class)->latestOfMany(); }

public function vetMetric() { return $this->hasOne(\App\Models\VetMetric::class); }
public function vetVisits() { return $this->hasMany(\App\Models\VetVisit::class)->orderByDesc('visit_date'); }
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


}
