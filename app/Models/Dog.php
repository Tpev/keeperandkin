<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Jetstream\Team;     // Jetstream's built-in team model

class Dog extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'name',
        'breed',
        'age',
        'sex',
        'description',
    ];

    /* Relationships */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
