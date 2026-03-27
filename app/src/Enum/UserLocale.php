<?php

namespace App\Enum;

use App\Enum\Trait\EnumValuesTrait;

enum UserLocale: string
{
    use EnumValuesTrait;

    case EN = 'en';
    case PL = 'pl';
}
