<?php

namespace App\Dto\Response;

use App\Dto\ResponseDtoInterface;
use App\Entity\Consumer;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminConsumerItemResponse')]
readonly class AdminConsumerItemResponse implements ResponseDtoInterface
{
    public function __construct(
        #[OA\Property(example: '550e8400-e29b-41d4-a716-446655440000')]
        public string $id,
        #[OA\Property(example: 'iPhone 15', nullable: true)]
        public ?string $deviceName,
        #[OA\Property(example: 'iPhone15,2', nullable: true)]
        public ?string $deviceModel,
        #[OA\Property(example: 'iOS', nullable: true)]
        public ?string $deviceOs,
        #[OA\Property(example: '17.0', nullable: true)]
        public ?string $deviceOsVersion,
        #[OA\Property(example: '2026-04-02 12:00:00')]
        public string $createdAt,
        #[OA\Property(example: '2026-04-02 14:00:00', nullable: true)]
        public ?string $lastActiveAt,
        #[OA\Property(example: 3)]
        public int $activeSubscriptions,
    ) {}

    public static function fromEntity(Consumer $c, int $activeSubscriptions): self
    {
        return new self(
            (string) $c->getId(),
            $c->getDeviceName(),
            $c->getDeviceModel(),
            $c->getDeviceOs(),
            $c->getDeviceOsVersion(),
            $c->getCreatedAt()->format('Y-m-d H:i:s'),
            $c->getLastActiveAt()?->format('Y-m-d H:i:s'),
            $activeSubscriptions,
        );
    }
}
