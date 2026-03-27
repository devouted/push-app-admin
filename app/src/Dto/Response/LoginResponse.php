<?php

namespace App\Dto\Response;

use App\Dto\ResponseDtoInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'LoginResponse')]
class LoginResponse implements ResponseDtoInterface
{
    public function __construct(
        #[OA\Property(example: 'eyJ0eXAiOiJKV1QiLCJhbGc...')]
        public readonly string $token,
        #[OA\Property]
        public readonly UserResponse $user
    ) {}
}
