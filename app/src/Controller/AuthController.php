<?php

namespace App\Controller;

use App\Dto\Request\LoginRequest;
use App\Dto\Response\ErrorResponse;
use App\Dto\Response\LoginResponse;
use App\Dto\Response\UserResponse;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class AuthController extends DefaultController
{
    public function __construct(
        private readonly UserRepository              $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly JWTTokenManagerInterface    $jwtManager,
        private readonly TranslatorInterface         $translator
    )
    {
    }

    #[Route('/auth/login', name: 'auth_login', methods: ['POST'])]
    #[OA\Post(
        path: '/api/auth/login',
        description: 'User authentication',
        summary: 'Login with email and password',
        security: []
    )]
    #[OA\RequestBody(content: new Model(type: LoginRequest::class))]
    #[OA\Response(
        response: 200,
        description: 'JWT token and user data',
        content: new Model(type: LoginResponse::class)
    )]
    #[OA\Response(response: 401, description: 'Invalid credentials', content: new Model(type: ErrorResponse::class))]
    #[OA\Response(response: 422, description: 'Validation error', content: new Model(type: ErrorResponse::class))]
    #[OA\Tag(name: 'Authentication')]
    public function login(#[MapRequestPayload] LoginRequest $loginRequest): JsonResponse
    {
        $user = $this->userRepository->findOneBy(['email' => $loginRequest->email]);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $loginRequest->password)) {
            throw new UnauthorizedHttpException('', $this->translator->trans('user.invalid_credentials', [], 'validators'));
        }

        $token = $this->jwtManager->create($user);

        $response = new LoginResponse(
            $token,
            UserResponse::fromEntity($user)
        );

        return $this->response($response);
    }
}
