<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/Evaluation.php
class Evaluation extends Model
{
    protected $fillable = ['dog_id', 'user_id', 'score', 'answers', 'red_flags','category_scores'];

protected $casts = [
    'answers'         => 'array',
    'red_flags'       => 'array',
    'category_scores' => 'array',
];


    public function dog()  { return $this->belongsTo(Dog::class); }
    public function user() { return $this->belongsTo(User::class); }
}


