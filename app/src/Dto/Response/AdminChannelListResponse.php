<?php

namespace App\Dto\Response;

use App\Dto\ResponseDtoInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminChannelListResponse')]
readonly class AdminChannelListResponse implements ResponseDtoInterface
{
    public function __construct(
        #[OA\Property(type: 'array', items: new OA\Items(ref: new OA\Schema(schema: 'AdminChannelListItemResponse')))]
        public array $items,
        #[OA\Property(example: 10)]
        public int $total,
        #[OA\Property(example: 1)]
        public int $page,
        #[OA\Property(example: 20)]
        public int $limit,
    ) {}
}
