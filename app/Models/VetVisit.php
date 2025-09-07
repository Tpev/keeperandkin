<?php

// app/Models/VetVisit.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VetVisit extends Model
{
    protected $fillable = ['dog_id','visit_date','reason','outcome','weight'];
    protected $casts = ['visit_date' => 'date'];
    public function dog() { return $this->belongsTo(Dog::class); }
}
