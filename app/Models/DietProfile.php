<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DietProfile extends Model
{
    protected $fillable = [
        'dog_id','food_brand','food_name','food_type','daily_calories',
        'meals_per_day','portion_grams_per_meal','allergies','supplements',
        'notes','last_reviewed_at',
    ];

    protected $casts = [
        'allergies' => 'array',
        'supplements' => 'array',
        'last_reviewed_at' => 'datetime',
    ];

    public function dog() { return $this->belongsTo(Dog::class); }
}
