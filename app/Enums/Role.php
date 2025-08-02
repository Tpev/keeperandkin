<?php

// app/Enums/Role.php
namespace App\Enums;

enum Role: string
{
    case ADMIN         = 'admin';
    case SHELTER_ADMIN = 'shelter_admin';
    case USER          = 'user';
}
