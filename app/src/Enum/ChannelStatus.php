<?php

namespace App\Enum;

use App\Enum\Trait\EnumValuesTrait;

enum ChannelStatus: string
{
    use EnumValuesTrait;

    case ACTIVE = 'active';
    case BLOCKED = 'blocked';
    case INACTIVE = 'inactive';
}
