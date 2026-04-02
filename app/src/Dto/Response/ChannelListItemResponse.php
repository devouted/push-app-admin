<?php

namespace App\Dto\Response;

use App\Dto\ResponseDtoInterface;
use App\Entity\Channel;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'ChannelListItemResponse')]
readonly class ChannelListItemResponse implements ResponseDtoInterface
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
        #[OA\Property(example: true)]
        public bool $isPublic,
        #[OA\Property(example: 1000, nullable: true)]
        public ?int $maxSubscribers,
        #[OA\Property(example: '2026-04-02 12:00:00')]
        public string $createdAt,
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
            $channel->isPublic(),
            $channel->getMaxSubscribers(),
            $channel->getCreatedAt()->format('Y-m-d H:i:s'),
        );
    }
}
