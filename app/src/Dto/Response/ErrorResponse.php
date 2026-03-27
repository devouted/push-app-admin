<?php

namespace App\Dto\Response;

use App\Dto\ResponseDtoInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'ErrorResponse')]
readonly class ErrorResponse implements ResponseDtoInterface
{
    public function __construct(
        #[OA\Property(example: 404)]
        public int $code,
        #[OA\Property(example: 'Resource not found')]
        public string $message,
        #[OA\Property(example: 'NotFoundError')]
        public string $type,
        #[OA\Property(example: [])]
        public array $details = []
    ) {}
}
