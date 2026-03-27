<?php

namespace App\Dto\Response;

use App\Dto\ResponseDtoInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'TranslationsResponse')]
readonly class TranslationsResponse implements ResponseDtoInterface
{
    public function __construct(
        #[OA\Property(type: 'object')]
        public array $messages,
        #[OA\Property(type: 'object')]
        public array $validators,
        #[OA\Property(type: 'object')]
        public array $security
    ) {}
}
