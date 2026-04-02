<?php

namespace App\Dto\Response;

use App\Dto\ResponseDtoInterface;
use App\Entity\Channel;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'ChannelResponse')]
readonly class ChannelResponse implements ResponseDtoInterface
{
    public function __construct(
        #[OA\Property(example: '550e8400-e29b-41d4-a716-446655440000')]
        public string $id,
        #[OA\Property(example: 'My Channel')]
        public string $name,
        #[OA\Property(example: 'Channel description', nullable: true)]
        public ?string $description,
        #[OA\Property(example: 'news', nullable: true)]
        public ?string $category,
        #[OA\Property(example: 'https://example.com/icon.png', nullable: true)]
        public ?string $icon,
        #[OA\Property(example: 'pl')]
        public string $language,
        #[OA\Property(example: 'active')]
        public string $status,
        #[OA\Property(example: null, nullable: true)]
        public ?string $blockedReason,
        #[OA\Property(example: true)]
        public bool $isPublic,
        #[OA\Property(example: 1000, nullable: true)]
        public ?int $maxSubscribers,
        #[OA\Property(example: 7)]
        public int $inactivityTimeoutDays,
        #[OA\Property(example: 'a1b2c3d4e5f6...')]
        public string $apiKey,
        #[OA\Property(example: '2026-04-02 12:00:00')]
        public string $createdAt,
        #[OA\Property(example: '2026-04-02 12:00:00')]
        public string $updatedAt,
    ) {}

    public static function fromEntity(Channel $channel): self
    {
        return new self(
            (string) $channel->getId(),
            $channel->getName(),
            $channel->getDescription(),
            $channel->getCategory(),
            $channel->getIcon(),
            $channel->getLanguage(),
            $channel->getStatus()->value,
            $channel->getBlockedReason(),
            $channel->isPublic(),
            $channel->getMaxSubscribers(),
            $channel->getInactivityTimeoutDays(),
            $channel->getApiKey(),
            $channel->getCreatedAt()->format('Y-m-d H:i:s'),
            $channel->getUpdatedAt()->format('Y-m-d H:i:s'),
        );
    }
}
