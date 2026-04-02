<?php

namespace App\Dto\Response;

use App\Dto\ResponseDtoInterface;
use App\Entity\Notification;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'NotificationDetailResponse')]
readonly class NotificationDetailResponse implements ResponseDtoInterface
{
    public function __construct(
        #[OA\Property(example: '550e8400-e29b-41d4-a716-446655440000')]
        public string $id,
        #[OA\Property(example: 'New update available')]
        public string $title,
        #[OA\Property(example: 'Check out the latest features')]
        public string $body,
        #[OA\Property(example: 'https://example.com/image.png', nullable: true)]
        public ?string $imageUrl,
        #[OA\Property(nullable: true)]
        public ?array $extraData,
        #[OA\Property]
        public NotificationChannelResponse $channel,
        #[OA\Property(example: '2026-04-02 12:00:00')]
        public string $createdAt,
    ) {}

    public static function fromEntity(Notification $notification): self
    {
        $channel = $notification->getChannel();

        return new self(
            (string) $notification->getId(),
            $notification->getTitle(),
            $notification->getBody(),
            $notification->getImageUrl(),
            $notification->getExtraData(),
            new NotificationChannelResponse(
                (string) $channel->getId(),
                $channel->getName(),
                $channel->getIcon(),
            ),
            $notification->getCreatedAt()->format('Y-m-d H:i:s'),
        );
    }
}
