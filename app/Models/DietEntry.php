<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DietEntry extends Model
{
    protected $fillable = [
        'dog_id','fed_at','meal','food','grams','calories','appetite','comment',
    ];

    protected $casts = [
        'fed_at' => 'datetime',
    ];

    public function dog() { return $this->belongsTo(Dog::class); }
}
