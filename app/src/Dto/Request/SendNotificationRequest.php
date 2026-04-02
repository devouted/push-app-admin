<?php

namespace App\Dto\Request;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(schema: 'SendNotificationRequest')]
readonly class SendNotificationRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        #[OA\Property(example: 'New update available')]
        public string $title,

        #[Assert\NotBlank]
        #[OA\Property(example: 'Check out the latest features')]
        public string $body,

        #[Assert\Length(max: 255)]
        #[OA\Property(example: 'https://example.com/image.png', nullable: true)]
        public ?string $imageUrl = null,

        #[OA\Property(example: '{"action": "open_url", "url": "https://example.com"}', nullable: true)]
        public ?array $extraData = null,
    ) {}
}
