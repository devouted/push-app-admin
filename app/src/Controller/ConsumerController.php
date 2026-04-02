<?php

namespace App\Controller;

use App\Dto\Request\RegisterConsumerRequest;
use App\Dto\Request\UpdateConsumerTokenRequest;
use App\Dto\Response\ConsumerRegisterResponse;
use App\Dto\Response\ConsumerTokenResponse;
use App\Dto\Response\ErrorResponse;
use App\Entity\Consumer;
use App\Repository\ConsumerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/consumers')]
class ConsumerController extends DefaultController
{
    public function __construct(
        private readonly ConsumerRepository $consumerRepository,
        private readonly EntityManagerInterface $em,
    ) {}

    #[Route('/register', name: 'consumer_register', methods: ['POST'])]
    #[OA\Post(
        path: '/api/consumers/register',
        summary: 'Register a consumer device',
        description: 'Registers a new consumer or updates device data if expo_token already exists. Public endpoint, no auth required.'
    )]
    #[OA\RequestBody(content: new Model(type: RegisterConsumerRequest::class))]
    #[OA\Response(response: 201, description: 'Consumer registered', content: new Model(type: ConsumerRegisterResponse::class))]
    #[OA\Response(response: 200, description: 'Consumer already exists, device data updated', content: new Model(type: ConsumerRegisterResponse::class))]
    #[OA\Tag(name: 'Consumer')]
    public function register(#[MapRequestPayload] RegisterConsumerRequest $request): JsonResponse
    {
        $existing = $this->consumerRepository->findByExpoToken($request->expo_token);

        if ($existing) {
            $existing->setDeviceName($request->device_name);
            $existing->setDeviceModel($request->device_model);
            $existing->setDeviceOs($request->device_os);
            $existing->setDeviceOsVersion($request->device_os_version);
            $existing->setLastActiveAt(new \DateTimeImmutable());
            $this->em->flush();

            return $this->response(ConsumerRegisterResponse::fromEntity($existing));
        }

        $consumer = new Consumer();
        $consumer->setExpoToken($request->expo_token);
        $consumer->setDeviceName($request->device_name);
        $consumer->setDeviceModel($request->device_model);
        $consumer->setDeviceOs($request->device_os);
        $consumer->setDeviceOsVersion($request->device_os_version);

        $this->em->persist($consumer);
        $this->em->flush();

        return $this->response(ConsumerRegisterResponse::fromEntity($consumer), 201);
    }

    #[Route('/{uuid}/token', name: 'consumer_update_token', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/consumers/{uuid}/token',
        summary: 'Update consumer Expo token',
        description: 'Updates the Expo push token for an existing consumer. Public endpoint, no auth required.'
    )]
    #[OA\RequestBody(content: new Model(type: UpdateConsumerTokenRequest::class))]
    #[OA\Response(response: 200, description: 'Token updated', content: new Model(type: ConsumerTokenResponse::class))]
    #[OA\Response(response: 404, description: 'Consumer not found', content: new Model(type: ErrorResponse::class))]
    #[OA\Tag(name: 'Consumer')]
    public function updateToken(string $uuid, #[MapRequestPayload] UpdateConsumerTokenRequest $request): JsonResponse
    {
        $consumer = $this->consumerRepository->find($uuid);

        if (!$consumer) {
            throw $this->createNotFoundException('Consumer not found');
        }

        $consumer->setExpoToken($request->expo_token);
        $consumer->setLastActiveAt(new \DateTimeImmutable());
        $this->em->flush();

        return $this->response(ConsumerTokenResponse::fromEntity($consumer));
    }
}
