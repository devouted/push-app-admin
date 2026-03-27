<?php

namespace App\Enum;

use App\Enum\Trait\EnumValuesTrait;

enum UserRole: string
{
    use EnumValuesTrait;

    case ROLE_USER = 'ROLE_USER';
    case ROLE_ADMIN = 'ROLE_ADMIN';
}
