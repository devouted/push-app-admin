<?php

namespace App\Dto\Response;

use App\Dto\ResponseDtoInterface;
use App\Entity\Notification;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'ClientNotificationItemResponse')]
readonly class ClientNotificationItemResponse implements ResponseDtoInterface
{
    public function __construct(
        #[OA\Property(example: '550e8400-e29b-41d4-a716-446655440000')]
        public string $id,
        #[OA\Property(example: 'New update')]
        public string $title,
        #[OA\Property(example: 'Check out the latest features')]
        public string $body,
        #[OA\Property(example: false)]
        public bool $isTest,
        #[OA\Property(example: '2026-04-02 12:00:00')]
        public string $createdAt,
    ) {}

    public static function fromEntity(Notification $n): self
    {
        return new self(
            (string) $n->getId(),
            $n->getTitle(),
            $n->getBody(),
            $n->isTest(),
            $n->getCreatedAt()->format('Y-m-d H:i:s'),
        );
    }
}
