<?php

namespace App\Dto\Response;

use App\Dto\ResponseDtoInterface;
use App\Entity\User;
use App\Enum\UserLocale;
use OpenApi\Attributes as OA;
use Symfony\Component\Security\Core\User\UserInterface;

#[OA\Schema(schema: 'UserResponse')]
readonly class UserResponse implements ResponseDtoInterface
{
    public function __construct(
        #[OA\Property(example: 1)]
        public int     $id,
        #[OA\Property(example: 'user@example.com')]
        public string  $email,
        #[OA\Property(type: 'array', items: new OA\Items(type: 'string'), example: ['ROLE_USER'])]
        public array   $roles,
        #[OA\Property(example: null, nullable: true)]
        public ?string $deletedAt,
        #[OA\Property(example: 'en')]
        public string  $locale = UserLocale::EN->value
    ) {}

    public static function fromEntity(User|UserInterface $user): self
    {
        return new self(
            $user->getId(),
            $user->getEmail(),
            $user->getRoles(),
            $user->getDeletedAt()?->format('Y-m-d H:i:s'),
            $user->getLocale()
        );
    }
}
