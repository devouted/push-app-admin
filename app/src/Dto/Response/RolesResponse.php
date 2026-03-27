<?php

namespace App\Dto\Response;

use App\Dto\ResponseDtoInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'RolesResponse')]
readonly class RolesResponse implements ResponseDtoInterface
{
    public function __construct(
        #[OA\Property(type: 'array', items: new OA\Items(type: 'string'), example: ['ROLE_USER', 'ROLE_ADMIN'])]
        public array $roles
    ) {}
}
