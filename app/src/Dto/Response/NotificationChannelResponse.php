<?php

namespace App\Dto\Response;

use App\Dto\ResponseDtoInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'NotificationChannelResponse')]
readonly class NotificationChannelResponse implements ResponseDtoInterface
{
    public function __construct(
        #[OA\Property(example: '550e8400-e29b-41d4-a716-446655440000')]
        public string $id,
        #[OA\Property(example: 'My Channel')]
        public string $name,
        #[OA\Property(example: 'https://example.com/icon.png', nullable: true)]
        public ?string $icon,
    ) {}
}
