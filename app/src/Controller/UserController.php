<?php

namespace App\Controller;

use App\Dto\Request\UpdateLocaleRequest;
use App\Dto\Response\UserResponse;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/users')]
class UserController extends DefaultController
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    #[Route('/me', name: 'user_me', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[OA\Get(path: '/api/users/me', description: 'Get current user information', summary: 'Get authenticated user data')]
    #[OA\Response(response: 200, description: 'User data', content: new Model(type: UserResponse::class))]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Tag(name: 'User')]
    public function me(): JsonResponse
    {
        return $this->response(UserResponse::fromEntity($this->getUser()));
    }

    #[Route('/me/locale', name: 'user_me_locale', methods: ['PATCH'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[OA\Patch(path: '/api/users/me/locale', description: 'Change locale for authenticated user', summary: 'Update user locale')]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [new OA\Property(property: 'locale', type: 'string', example: 'pl')]))]
    #[OA\Response(response: 200, description: 'Locale updated', content: new Model(type: UserResponse::class))]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Tag(name: 'User')]
    public function updateLocale(#[MapRequestPayload] UpdateLocaleRequest $request): JsonResponse
    {
        $user = $this->getUser();
        $user->setLocale($request->locale);
        $this->em->flush();

        return $this->response(UserResponse::fromEntity($user));
    }
}
