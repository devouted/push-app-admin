<?php

namespace App\Controller;

use App\Dto\Request\CreateChannelRequest;
use App\Dto\Request\UpdateChannelRequest;
use App\Dto\Response\ChannelListItemResponse;
use App\Dto\Response\ChannelListResponse;
use App\Dto\Response\ChannelResponse;
use App\Dto\Response\ErrorResponse;
use App\Dto\Response\NotificationSentResponse;
use App\Entity\Channel;
use App\Entity\Notification;
use App\Message\SendPushNotification;
use App\Repository\ChannelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/client/channels')]
#[IsGranted('ROLE_CLIENT')]
class ChannelController extends DefaultController
{
    public function __construct(
        private readonly ChannelRepository $channelRepository,
        private readonly EntityManagerInterface $em,
        private readonly MessageBusInterface $bus,
    ) {}

    #[Route('', name: 'client_channels_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/client/channels',
        summary: 'List own channels',
        description: 'Returns paginated list of channels owned by the authenticated client. API key is not included in list response.'
    )]
    #[OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1))]
    #[OA\Parameter(name: 'limit', in: 'query', schema: new OA\Schema(type: 'integer', default: 20))]
    #[OA\Response(response: 200, description: 'Channels list', content: new Model(type: ChannelListResponse::class))]
    #[OA\Tag(name: 'Client Channels')]
    public function list(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $page = max(1, $request->query->getInt('page', 1));
        $limit = min(100, max(1, $request->query->getInt('limit', 20)));

        $channels = $this->channelRepository->findByOwner($user, $page, $limit);
        $total = $this->channelRepository->countByOwner($user);

        return $this->response(new ChannelListResponse(
            array_map(fn(Channel $c) => ChannelListItemResponse::fromEntity($c), $channels),
            $total,
            $page,
            $limit,
        ));
    }

    #[Route('', name: 'client_channels_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/client/channels',
        summary: 'Create a channel',
        description: 'Creates a new notification channel with auto-generated API key'
    )]
    #[OA\RequestBody(content: new Model(type: CreateChannelRequest::class))]
    #[OA\Response(response: 201, description: 'Channel created', content: new Model(type: ChannelResponse::class))]
    #[OA\Response(response: 400, description: 'Validation error', content: new Model(type: ErrorResponse::class))]
    #[OA\Tag(name: 'Client Channels')]
    public function create(#[MapRequestPayload] CreateChannelRequest $request): JsonResponse
    {
        $channel = new Channel();
        $channel->setOwner($this->getUser());
        $channel->setName($request->name);
        $channel->setDescription($request->description);
        $channel->setCategory($request->category);
        $channel->setIcon($request->icon);
        $channel->setLanguage($request->language);
        $channel->setIsPublic($request->isPublic);
        $channel->setMaxSubscribers($request->maxSubscribers);
        $channel->setInactivityTimeoutDays($request->inactivityTimeoutDays);

        $this->em->persist($channel);
        $this->em->flush();

        return $this->response(ChannelResponse::fromEntity($channel), 201);
    }

    #[Route('/{id}', name: 'client_channels_get', methods: ['GET'])]
    #[OA\Get(
        path: '/api/client/channels/{id}',
        summary: 'Get channel details',
        description: 'Returns full channel details including API key and blocked_reason'
    )]
    #[OA\Response(response: 200, description: 'Channel details', content: new Model(type: ChannelResponse::class))]
    #[OA\Response(response: 404, description: 'Channel not found', content: new Model(type: ErrorResponse::class))]
    #[OA\Tag(name: 'Client Channels')]
    public function get(string $id): JsonResponse
    {
        return $this->response(ChannelResponse::fromEntity($this->findOwnChannel($id)));
    }

    #[Route('/{id}', name: 'client_channels_update', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/client/channels/{id}',
        summary: 'Update a channel',
        description: 'Updates fields of a channel owned by the authenticated client'
    )]
    #[OA\RequestBody(content: new Model(type: UpdateChannelRequest::class))]
    #[OA\Response(response: 200, description: 'Channel updated', content: new Model(type: ChannelResponse::class))]
    #[OA\Response(response: 404, description: 'Channel not found', content: new Model(type: ErrorResponse::class))]
    #[OA\Tag(name: 'Client Channels')]
    public function update(string $id, #[MapRequestPayload] UpdateChannelRequest $request): JsonResponse
    {
        $channel = $this->findOwnChannel($id);

        if ($request->name !== null) $channel->setName($request->name);
        if ($request->description !== null) $channel->setDescription($request->description);
        if ($request->category !== null) $channel->setCategory($request->category);
        if ($request->icon !== null) $channel->setIcon($request->icon);
        if ($request->language !== null) $channel->setLanguage($request->language);
        if ($request->isPublic !== null) $channel->setIsPublic($request->isPublic);
        if ($request->maxSubscribers !== null) $channel->setMaxSubscribers($request->maxSubscribers);
        if ($request->inactivityTimeoutDays !== null) $channel->setInactivityTimeoutDays($request->inactivityTimeoutDays);

        $this->em->flush();

        return $this->response(ChannelResponse::fromEntity($channel));
    }

    #[Route('/{id}', name: 'client_channels_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/client/channels/{id}',
        summary: 'Delete a channel',
        description: 'Soft-deletes a channel owned by the authenticated client'
    )]
    #[OA\Response(response: 204, description: 'Channel deleted')]
    #[OA\Response(response: 404, description: 'Channel not found', content: new Model(type: ErrorResponse::class))]
    #[OA\Tag(name: 'Client Channels')]
    public function delete(string $id): JsonResponse
    {
        $channel = $this->findOwnChannel($id);
        $channel->setDeletedAt(new \DateTimeImmutable());
        $this->em->flush();

        return new JsonResponse(null, 204);
    }

    #[Route('/{id}/rotate-key', name: 'client_channels_rotate_key', methods: ['POST'])]
    #[OA\Post(
        path: '/api/client/channels/{id}/rotate-key',
        summary: 'Rotate channel API key',
        description: 'Generates a new API key for the channel, invalidating the old one'
    )]
    #[OA\Response(response: 200, description: 'New API key generated', content: new Model(type: ChannelResponse::class))]
    #[OA\Response(response: 404, description: 'Channel not found', content: new Model(type: ErrorResponse::class))]
    #[OA\Tag(name: 'Client Channels')]
    public function rotateKey(string $id): JsonResponse
    {
        $channel = $this->findOwnChannel($id);
        $channel->regenerateApiKey();
        $this->em->flush();

        return $this->response(ChannelResponse::fromEntity($channel));
    }

    #[Route('/{id}/test', name: 'client_channels_test', methods: ['POST'])]
    #[OA\Post(
        path: '/api/client/channels/{id}/test',
        summary: 'Send test notification',
        description: 'Sends a test notification to channel subscribers. Uses default title/body if not provided.'
    )]
    #[OA\RequestBody(content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'title', type: 'string', example: 'Custom test title', nullable: true),
            new OA\Property(property: 'body', type: 'string', example: 'Custom test body', nullable: true),
        ]
    ))]
    #[OA\Response(response: 201, description: 'Test notification sent', content: new Model(type: NotificationSentResponse::class))]
    #[OA\Response(response: 404, description: 'Channel not found', content: new Model(type: ErrorResponse::class))]
    #[OA\Tag(name: 'Client Channels')]
    public function test(string $id, Request $request): JsonResponse
    {
        $channel = $this->findOwnChannel($id);

        $data = json_decode($request->getContent(), true) ?? [];
        $title = $data['title'] ?? 'Test kanalu ' . $channel->getName();
        $body = $data['body'] ?? 'To jest testowe powiadomienie';

        $notification = new Notification();
        $notification->setChannel($channel);
        $notification->setTitle($title);
        $notification->setBody($body);
        $notification->setIsTest(true);

        $this->em->persist($notification);
        $this->em->flush();

        $this->bus->dispatch(new SendPushNotification((string) $notification->getId()));

        return $this->response(new NotificationSentResponse(
            (string) $notification->getId(),
            0,
        ), 201);
    }

    private function findOwnChannel(string $id): Channel
    {
        $channel = $this->channelRepository->find($id);

        if (!$channel || $channel->isDeleted() || $channel->getOwner() !== $this->getUser()) {
            throw $this->createNotFoundException('Channel not found');
        }

        return $channel;
    }
}
