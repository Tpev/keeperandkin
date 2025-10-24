<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Contracts\Auth\Authenticatable as User;

class CertificationProgram extends Model
{
    protected $table = 'cert_programs';

    public const VIS_PUBLIC    = 'public';
    public const VIS_ROLEGATED = 'role_gated';

    public const DIFF_BEGINNER     = 'beginner';
    public const DIFF_INTERMEDIATE = 'intermediate';
    public const DIFF_ADVANCED     = 'advanced';

    public const DIFFICULTIES = [
        self::DIFF_BEGINNER,
        self::DIFF_INTERMEDIATE,
        self::DIFF_ADVANCED,
    ];

    protected $fillable = [
        'title',
        'slug',
        'description',
        'visibility_mode',
        'required_roles',
        'is_active',
        'difficulty',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'required_roles' => 'array',
    ];

public function flags(): BelongsToMany
{
    return $this->belongsToMany(
            TrainingFlag::class,
            'cert_program_flag',
            'cert_program_id',   // <- explicit foreignPivotKey (this model)
            'training_flag_id'   // <- explicit relatedPivotKey
        )
        ->withTimestamps()
        ->withPivot('position')
        ->orderBy('cert_program_flag.position');
}


    public function enrollments(): HasMany
    {
        return $this->hasMany(CertificationEnrollment::class, 'cert_program_id');
    }

    /** Analytics helpers (light, Phase 2) */
    public function countEnrolled(): int
    {
        return $this->enrollments()->where('status', 'enrolled')->count();
    }

    public function countInProgress(): int
    {
        return $this->enrollments()->where('status', 'in_progress')->count();
    }

    public function countCompleted(): int
    {
        return $this->enrollments()->where('status', 'completed')->count();
    }
	
	   /** Basic visibility check (Phase 3): public OR user has one of required roles */
    public function visibleTo(?User $user): bool
    {
        if (!$this->is_active) return false;

        if ($this->visibility_mode === self::VIS_PUBLIC) {
            return true;
        }

        // role_gated
        if (!$user) return false;
        $required = $this->required_roles ?? [];
        if (empty($required)) return true; // nothing specified => visible

        // Try common role shapes; adjust later if your app uses a different role system
        $userRoles = [];
        if (method_exists($user, 'getRoleNames')) {
            $userRoles = $user->getRoleNames()->toArray();
        } elseif (property_exists($user, 'roles') && is_array($user->roles)) {
            $userRoles = $user->roles;
        } elseif (isset($user->role)) {
            $userRoles = [$user->role];
        }

        $userRoles = array_map('strtolower', array_map('trim', $userRoles));
        $required  = array_map('strtolower', array_map('trim', $required));

        return count(array_intersect($userRoles, $required)) > 0;
    }
}
