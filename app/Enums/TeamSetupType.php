<?php

namespace App\Enums;

enum TeamSetupType:string {
    case SANCTUARY_RESCUE = 'sanctuary_rescue';
    case FOSTER_RESCUE    = 'foster_rescue';
    case ADOPTER          = 'adopter';
}
