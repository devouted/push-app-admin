<?php

namespace App\Dto\Response;

use App\Dto\ResponseDtoInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'PublicNotificationListResponse')]
readonly class PublicNotificationListResponse implements ResponseDtoInterface
{
    /**
     * @param PublicNotificationItemResponse[] $items
     */
    public function __construct(
        #[OA\Property(type: 'array', items: new OA\Items(ref: '#/components/schemas/PublicNotificationItemResponse'))]
        public array $items,
        #[OA\Property(example: 50)]
        public int $total,
        #[OA\Property(example: 1)]
        public int $page,
        #[OA\Property(example: 20)]
        public int $limit,
    ) {}
}
