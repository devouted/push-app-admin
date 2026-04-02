<?php

namespace App\Dto\Response;

use App\Dto\ResponseDtoInterface;
use App\Entity\Channel;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'PublicChannelItemResponse')]
readonly class PublicChannelItemResponse implements ResponseDtoInterface
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
        #[OA\Property(example: 42)]
        public int $subscribersCount,
    ) {}

    public static function fromEntity(Channel $channel, int $subscribersCount): self
    {
        return new self(
            (string) $channel->getId(),
            $channel->getName(),
            $channel->getDescription(),
            $channel->getCategory(),
            $channel->getIcon(),
            $channel->getLanguage(),
            $subscribersCount,
        );
    }
}
