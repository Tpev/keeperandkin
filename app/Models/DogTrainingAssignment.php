<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DogTrainingAssignment extends Model
{
    protected $fillable = [
        'dog_id','training_session_id','training_flag_id','evaluation_id',
        'status','started_at','completed_at','notes'
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function dog(): BelongsTo { return $this->belongsTo(Dog::class); }
    public function session(): BelongsTo { return $this->belongsTo(TrainingSession::class,'training_session_id'); }
    public function flag(): BelongsTo { return $this->belongsTo(TrainingFlag::class,'training_flag_id'); }
    public function evaluation(): BelongsTo { return $this->belongsTo(Evaluation::class); }
}
