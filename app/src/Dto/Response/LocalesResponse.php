<?php

namespace App\Dto\Response;

use App\Dto\ResponseDtoInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'LocalesResponse')]
readonly class LocalesResponse implements ResponseDtoInterface
{
    public function __construct(
        #[OA\Property(type: 'array', items: new OA\Items(type: 'string'), example: ['en', 'pl'])]
        public array $locales
    ) {}
}
