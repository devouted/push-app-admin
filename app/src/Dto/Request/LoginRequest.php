<?php

namespace App\Dto\Request;

use Symfony\Component\Validator\Constraints as Assert;

class LoginRequest
{
    #[Assert\NotBlank(message: 'user.email.required')]
    #[Assert\Email(message: 'user.email.invalid')]
    public string $email;

    #[Assert\NotBlank(message: 'user.password.required')]
    #[Assert\Length(min: 6, minMessage: 'user.password.too_short')]
    public string $password;
}
