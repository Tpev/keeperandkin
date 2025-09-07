<?php

// app/Models/VetMetric.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VetMetric extends Model
{
    protected $fillable = ['dog_id','current_weight','bcs','next_vaccine_date'];
    public function dog() { return $this->belongsTo(Dog::class); }
}
