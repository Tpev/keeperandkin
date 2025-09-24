<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DogTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'dog_id','from_team_id','to_team_id','to_email','status','token_hash','expires_at',
        'include_private_notes','include_adopter_pii','initiator_user_id','accepted_user_id',
        'accepted_at','declined_at','canceled_at',
        'count_evaluations','count_files','count_notes','meta',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at'=> 'datetime',
        'declined_at'=> 'datetime',
        'canceled_at'=> 'datetime',
        'include_private_notes' => 'bool',
        'include_adopter_pii'   => 'bool',
        'meta' => AsArrayObject::class,
    ];

    public function dog()        { return $this->belongsTo(Dog::class); }
    public function fromTeam()   { return $this->belongsTo(Team::class, 'from_team_id'); }
    public function toTeam()     { return $this->belongsTo(Team::class, 'to_team_id'); }
    public function initiator()  { return $this->belongsTo(User::class, 'initiator_user_id'); }
    public function acceptedBy() { return $this->belongsTo(User::class, 'accepted_user_id'); }

    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isExpired(): bool  { return now()->greaterThanOrEqualTo($this->expires_at); }
}
