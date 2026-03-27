<?php

namespace App\Controller;

use App\Dto\Request\AssignRolesRequest;
use App\Dto\Request\CreateUserRequest;
use App\Dto\Request\UpdateUserRequest;
use App\Dto\Response\ErrorResponse;
use App\Dto\Response\UserResponse;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
class UserManagementController extends DefaultController
{
    public function __construct(
        private readonly UserRepository              $userRepository,
        private readonly EntityManagerInterface      $em,
        private readonly UserPasswordHasherInterface $passwordHasher
    )
    {
    }

    #[Route('', name: 'admin_users_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/admin/users',
        summary: 'List all users'
    )]
    #[OA\Response(
        response: 200,
        description: 'Users list',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: UserResponse::class))
        )
    )]
    #[OA\Tag(name: 'User Management')]
    public function list(): JsonResponse
    {
        $users = $this->userRepository->findAll();
        return $this->json(array_map(fn($u) => UserResponse::fromEntity($u), $users));
    }

    #[OA\Get(
        path: '/api/admin/users/{id}',
        summary: 'Get user details'
    )]
    #[Route('/{id}', name: 'admin_users_get', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'User details',
        content: new Model(type: UserResponse::class)
    )]
    #[OA\Response(response: 404, description: 'User not found', content: new Model(type: ErrorResponse::class))]
    #[OA\Tag(name: 'User Management')]
    public function get(#[MapEntity(message: 'error.user_not_found')] User $user): JsonResponse
    {
        return $this->response(UserResponse::fromEntity($user));
    }

    #[Route('', name: 'admin_users_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/admin/users',
        summary: 'Create new user'
    )]
    #[OA\Response(
        response: 201,
        description: 'User created',
        content: new Model(type: UserResponse::class)
    )]
    #[OA\Response(response: 400, description: 'Validation error', content: new Model(type: ErrorResponse::class))]
    #[OA\Tag(name: 'User Management')]
    public function create(#[MapRequestPayload] CreateUserRequest $request): JsonResponse
    {
        $user = new User();
        $user->setEmail($request->email);
        $user->setRoles($request->roles);
        $user->setPassword($this->passwordHasher->hashPassword($user, $request->password));

        $this->em->persist($user);
        $this->em->flush();

        return $this->response(UserResponse::fromEntity($user), 201);
    }

    #[Route('/{id}', name: 'admin_users_update', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[OA\Put(
        path: '/api/admin/users/{id}',
        summary: 'Update user'
    )]
    #[OA\Response(
        response: 200,
        description: 'User updated',
        content: new Model(type: UserResponse::class)
    )]
    #[OA\Response(response: 404, description: 'User not found', content: new Model(type: ErrorResponse::class))]
    #[OA\Tag(name: 'User Management')]
    public function update(#[MapEntity(message: 'error.user_not_found')] User $user, #[MapRequestPayload] UpdateUserRequest $request): JsonResponse
    {
        if ($request->email) $user->setEmail($request->email);
        if ($request->roles) $user->setRoles($request->roles);
        if ($request->password) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $request->password));
        }

        $this->em->flush();
        return $this->response(UserResponse::fromEntity($user));
    }

    #[Route('/{id}', name: 'admin_users_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/admin/users/{id}',
        summary: 'Soft delete user'
    )]
    #[OA\Response(response: 204, description: 'User deleted')]
    #[OA\Response(response: 404, description: 'User not found', content: new Model(type: ErrorResponse::class))]
    #[OA\Tag(name: 'User Management')]
    public function delete(#[MapEntity(message: 'error.user_not_found')] User $user): JsonResponse
    {
        $user->setDeletedAt(new \DateTimeImmutable());
        $this->em->flush();

        return new JsonResponse(null, 204);
    }

    #[Route('/{id}/roles', name: 'admin_users_roles', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[OA\Post(
        path: '/api/admin/users/{id}/roles',
        summary: 'Assign roles to user'
    )]
    #[OA\Response(
        response: 200,
        description: 'Roles assigned',
        content: new Model(type: UserResponse::class)
    )]
    #[OA\Response(response: 404, description: 'User not found', content: new Model(type: ErrorResponse::class))]
    #[OA\Tag(name: 'User Management')]
    public function assignRoles(#[MapEntity(message: 'error.user_not_found')] User $user, #[MapRequestPayload] AssignRolesRequest $request): JsonResponse
    {
        $user->setRoles($request->roles);
        $this->em->flush();

        return $this->response(UserResponse::fromEntity($user));
    }
}
