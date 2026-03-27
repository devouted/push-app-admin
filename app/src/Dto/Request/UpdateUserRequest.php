<?php

namespace App\Dto\Request;

use App\Enum\UserRole;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateUserRequest
{
    #[Assert\Email(message: 'user.email.invalid')]
    public ?string $email = null;

    #[Assert\Length(min: 6, minMessage: 'user.password.too_short')]
    public ?string $password = null;

    #[Assert\All([
        new Assert\Choice(callback: [UserRole::class, 'getValues'], message: 'user.roles.invalid')
    ])]
    public ?array $roles = null;
}
