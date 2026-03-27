<?php

namespace App\Dto\Request;

use App\Enum\UserRole;
use Symfony\Component\Validator\Constraints as Assert;

class AssignRolesRequest
{
    #[Assert\NotBlank(message: 'user.roles.required')]
    #[Assert\All([
        new Assert\Choice(callback: [UserRole::class, 'getValues'], message: 'user.roles.invalid')
    ])]
    public array $roles;
}
