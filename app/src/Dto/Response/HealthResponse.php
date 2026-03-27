<?php

namespace App\Dto\Response;

use App\Dto\ResponseDtoInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'HealthResponse')]
readonly class HealthResponse implements ResponseDtoInterface
{
    public function __construct(
        #[OA\Property(example: '2026-02-10T12:00:00+00:00')]
        public string $timestamp,
        #[OA\Property(example: 'ok')]
        public string $status = "ok",
    ) {}
}
