<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificationEnrollment extends Model
{
    protected $table = 'cert_enrollments';

    protected $fillable = [
        'user_id',
        'cert_program_id',
        'status',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(CertificationProgram::class, 'cert_program_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
