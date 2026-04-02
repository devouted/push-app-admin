<?php

namespace App\Dto\Request;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(schema: 'UpdateChannelRequest')]
class UpdateChannelRequest
{
    #[Assert\Length(max: 255, maxMessage: 'channel.name.too_long')]
    #[OA\Property(example: 'Updated Channel Name', nullable: true)]
    public ?string $name = null;

    #[OA\Property(example: 'Updated description', nullable: true)]
    public ?string $description = null;

    #[Assert\Length(max: 100, maxMessage: 'channel.category.too_long')]
    #[OA\Property(example: 'updates', nullable: true)]
    public ?string $category = null;

    #[Assert\Length(max: 255, maxMessage: 'channel.icon.too_long')]
    #[OA\Property(example: 'https://example.com/new-icon.png', nullable: true)]
    public ?string $icon = null;

    #[Assert\Length(max: 5, maxMessage: 'channel.language.too_long')]
    #[OA\Property(example: 'en', nullable: true)]
    public ?string $language = null;

    #[OA\Property(example: false, nullable: true)]
    public ?bool $isPublic = null;

    #[Assert\Positive(message: 'channel.max_subscribers.positive')]
    #[OA\Property(example: 5000, nullable: true)]
    public ?int $maxSubscribers = null;

    #[Assert\Positive(message: 'channel.inactivity_timeout_days.positive')]
    #[OA\Property(example: 14, nullable: true)]
    public ?int $inactivityTimeoutDays = null;
}
