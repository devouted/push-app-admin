<?php

namespace App\Dto\Request;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(schema: 'RegisterConsumerRequest')]
readonly class RegisterConsumerRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[OA\Property(example: 'ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]')]
        public string $expo_token,

        #[OA\Property(example: 'iPhone 15', nullable: true)]
        public ?string $device_name = null,

        #[OA\Property(example: 'iPhone15,2', nullable: true)]
        public ?string $device_model = null,

        #[OA\Property(example: 'iOS', nullable: true)]
        public ?string $device_os = null,

        #[OA\Property(example: '17.4', nullable: true)]
        public ?string $device_os_version = null,
    ) {}
}
