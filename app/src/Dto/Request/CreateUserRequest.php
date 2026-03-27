<?php

namespace App\Dto\Request;

use App\Enum\UserRole;
use Symfony\Component\Validator\Constraints as Assert;

class CreateUserRequest
{
    #[Assert\NotBlank(message: 'user.email.required')]
    #[Assert\Email(message: 'user.email.invalid')]
    public string $email;

    #[Assert\NotBlank(message: 'user.password.required')]
    #[Assert\Length(min: 8, minMessage: 'user.password.too_short')]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,}$/',
        message: 'user.password.weak'
    )]
    public string $password;

    #[Assert\NotBlank(message: 'user.roles.required')]
    #[Assert\All([
        new Assert\Choice(callback: [UserRole::class, 'getValues'], message: 'user.roles.invalid')
    ])]
    public array $roles = [UserRole::ROLE_USER->value];
}
