<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuditLog extends Model
{
    use HasFactory;
    protected $fillable = ['event','subject_type','subject_id','actor_user_id','actor_team_id','context'];
    protected $casts = ['context' => 'array'];

    public function subject() { return $this->morphTo(); }
    public function actor()   { return $this->belongsTo(User::class, 'actor_user_id'); }
    public function team()    { return $this->belongsTo(Team::class, 'actor_team_id'); }
}
