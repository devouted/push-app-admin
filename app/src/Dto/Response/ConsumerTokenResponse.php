<?php

namespace App\Dto\Response;

use App\Dto\ResponseDtoInterface;
use App\Entity\Consumer;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'ConsumerTokenResponse')]
readonly class ConsumerTokenResponse implements ResponseDtoInterface
{
    public function __construct(
        #[OA\Property(example: '550e8400-e29b-41d4-a716-446655440000')]
        public string $uuid,

        #[OA\Property(example: '2026-04-02 12:00:00')]
        public string $updated_at,
    ) {}

    public static function fromEntity(Consumer $consumer): self
    {
        return new self(
            (string) $consumer->getId(),
            ($consumer->getLastActiveAt() ?? $consumer->getCreatedAt())->format('Y-m-d H:i:s'),
        );
    }
}
