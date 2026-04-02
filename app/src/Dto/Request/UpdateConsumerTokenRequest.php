<?php

namespace App\Dto\Request;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(schema: 'UpdateConsumerTokenRequest')]
readonly class UpdateConsumerTokenRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[OA\Property(example: 'ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]')]
        public string $expo_token,
    ) {}
}
