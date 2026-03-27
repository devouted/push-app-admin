<?php

namespace App\Enum\Trait;

trait EnumValuesTrait
{
    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
