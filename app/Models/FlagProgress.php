<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlagProgress extends Model
{
    protected $table = 'flag_user';
    protected $fillable = [
        'user_id', 'training_flag_id', 'status', 'started_at', 'completed_at', 'notes',
    ];
}
