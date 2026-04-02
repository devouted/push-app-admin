<?php

namespace App\Dto\Request;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(schema: 'BlockChannelRequest')]
readonly class BlockChannelRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 3)]
        #[OA\Property(example: 'Violation of terms of service')]
        public string $reason = '',
    ) {}
}
