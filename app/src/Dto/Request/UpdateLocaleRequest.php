<?php

namespace App\Dto\Request;

use App\Enum\UserLocale;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateLocaleRequest
{
    #[Assert\NotBlank]
    #[Assert\Choice(callback: [UserLocale::class, 'getValues'])]
    public string $locale;
}
