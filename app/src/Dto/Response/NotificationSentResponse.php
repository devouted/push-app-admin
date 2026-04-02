<?php

namespace App\Dto\Response;

use App\Dto\ResponseDtoInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'NotificationSentResponse')]
readonly class NotificationSentResponse implements ResponseDtoInterface
{
    public function __construct(
        #[OA\Property(example: '550e8400-e29b-41d4-a716-446655440000')]
        public string $notificationId,

        #[OA\Property(example: 42)]
        public int $subscribersCount,
    ) {}
}
