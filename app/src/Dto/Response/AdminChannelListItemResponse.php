<?php

namespace App\Dto\Response;

use App\Dto\ResponseDtoInterface;
use App\Entity\Channel;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminChannelListItemResponse')]
readonly class AdminChannelListItemResponse implements ResponseDtoInterface
{
    public function __construct(
        #[OA\Property(example: '550e8400-e29b-41d4-a716-446655440000')]
        public string $id,
        #[OA\Property(example: 'My Channel')]
        public string $name,
        #[OA\Property(example: 'active')]
        public string $status,
        #[OA\Property(example: 'Violation of terms', nullable: true)]
        public ?string $blockedReason,
        #[OA\Property(example: 1)]
        public int $ownerId,
        #[OA\Property(example: 'user@example.com')]
        public string $ownerEmail,
        #[OA\Property(example: '2026-04-02 12:00:00')]
        public string $createdAt,
    ) {}

    public static function fromEntity(Channel $channel): self
    {
        return new self(
            (string) $channel->getId(),
            $channel->getName(),
            $channel->getStatus()->value,
            $channel->getBlockedReason(),
            $channel->getOwner()->getId(),
            $channel->getOwner()->getEmail(),
            $channel->getCreatedAt()->format('Y-m-d H:i:s'),
        );
    }
}
