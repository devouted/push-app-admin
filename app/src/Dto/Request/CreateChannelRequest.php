<?php

namespace App\Dto\Request;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(schema: 'CreateChannelRequest')]
class CreateChannelRequest
{
    #[Assert\NotBlank(message: 'channel.name.required')]
    #[Assert\Length(max: 255, maxMessage: 'channel.name.too_long')]
    #[OA\Property(example: 'My Channel')]
    public string $name;

    #[OA\Property(example: 'Channel description', nullable: true)]
    public ?string $description = null;

    #[Assert\Length(max: 100, maxMessage: 'channel.category.too_long')]
    #[OA\Property(example: 'news', nullable: true)]
    public ?string $category = null;

    #[Assert\Length(max: 255, maxMessage: 'channel.icon.too_long')]
    #[OA\Property(example: 'https://example.com/icon.png', nullable: true)]
    public ?string $icon = null;

    #[Assert\Length(max: 5, maxMessage: 'channel.language.too_long')]
    #[OA\Property(example: 'pl')]
    public string $language = 'pl';

    #[OA\Property(example: true)]
    public bool $isPublic = true;

    #[Assert\Positive(message: 'channel.max_subscribers.positive')]
    #[OA\Property(example: 1000, nullable: true)]
    public ?int $maxSubscribers = null;

    #[Assert\Positive(message: 'channel.inactivity_timeout_days.positive')]
    #[OA\Property(example: 7)]
    public int $inactivityTimeoutDays = 7;
}
