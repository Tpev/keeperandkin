<?php

namespace App\Enums;

enum TeamRole:string {
    // Sanctuary / Rescue
    case RESCUE_ADMIN     = 'rescue_admin';
    case RESCUE_STAFF     = 'rescue_staff';
    case RESCUE_VOLUNTEER = 'rescue_volunteer';

    // Foster-based rescue
    case FOSTER_ADMIN   = 'foster_admin';
    case FOSTER_STAFF   = 'foster_staff';
    case FOSTER_FOSTER  = 'foster_foster';

    // Adopter
    case ADOPTER        = 'adopter';
}
