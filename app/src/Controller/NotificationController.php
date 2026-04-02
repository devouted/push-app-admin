<?php

namespace App\Controller;

use App\Dto\Request\SendNotificationRequest;
use App\Dto\Response\ErrorResponse;
use App\Dto\Response\NotificationDetailResponse;
use App\Dto\Response\NotificationSentResponse;
use App\Entity\Notification;
use App\Enum\ChannelStatus;
use App\Message\SendPushNotification;
use App\Message\UpdateSubscriptionActivity;
use App\Repository\ChannelRepository;
use App\Repository\ConsumerRepository;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class NotificationController extends DefaultController
{
    public function __construct(
        private readonly ChannelRepository $channelRepository,
        private readonly NotificationRepository $notificationRepository,
        private readonly ConsumerRepository $consumerRepository,
        private readonly EntityManagerInterface $em,
        private readonly MessageBusInterface $bus,
    ) {}

    #[Route('/channels/{id}/notify', name: 'channel_notify', methods: ['POST'])]
    #[OA\Post(
        path: '/api/channels/{id}/notify',
        summary: 'Send notification to channel subscribers',
        description: 'Accepts a notification payload, saves it and queues push delivery to all channel subscribers. Authorized by X-Api-Key header.'
    )]
    #[OA\Parameter(name: 'id', in: 'path', schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\Header(header: 'X-Api-Key', schema: new OA\Schema(type: 'string'))]
    #[OA\RequestBody(content: new Model(type: SendNotificationRequest::class))]
    #[OA\Response(response: 201, description: 'Notification created and queued', content: new Model(type: NotificationSentResponse::class))]
    #[OA\Response(response: 401, description: 'Missing or invalid API key', content: new Model(type: ErrorResponse::class))]
    #[OA\Response(response: 403, description: 'Channel blocked or inactive', content: new Model(type: ErrorResponse::class))]
    #[OA\Response(response: 404, description: 'Channel not found', content: new Model(type: ErrorResponse::class))]
    #[OA\Response(response: 422, description: 'Validation error', content: new Model(type: ErrorResponse::class))]
    #[OA\Tag(name: 'Notifications')]
    public function notify(
        string $id,
        Request $request,
        #[MapRequestPayload] SendNotificationRequest $dto,
    ): JsonResponse {
        $apiKey = $request->headers->get('X-Api-Key');
        if (!$apiKey) {
            return $this->response(new ErrorResponse(401, 'Missing X-Api-Key header', 'AuthenticationError'), 401);
        }

        $channel = $this->channelRepository->find($id);
        if (!$channel || $channel->isDeleted()) {
            return $this->response(new ErrorResponse(404, 'Channel not found', 'NotFoundError'), 404);
        }

        if ($channel->getApiKey() !== $apiKey) {
            return $this->response(new ErrorResponse(401, 'Invalid API key', 'AuthenticationError'), 401);
        }

        if ($channel->getStatus() !== ChannelStatus::ACTIVE) {
            return $this->response(new ErrorResponse(403, 'Channel is ' . $channel->getStatus()->value, 'ForbiddenError'), 403);
        }

        $notification = new Notification();
        $notification->setChannel($channel);
        $notification->setTitle($dto->title);
        $notification->setBody($dto->body);
        $notification->setImageUrl($dto->imageUrl);
        $notification->setExtraData($dto->extraData);

        $this->em->persist($notification);
        $this->em->flush();

        $this->bus->dispatch(new SendPushNotification((string) $notification->getId()));

        return $this->response(new NotificationSentResponse(
            (string) $notification->getId(),
            0, // TODO: count subscribers when Subscription entity exists (task #2433)
        ), 201);
    }

    #[Route('/notifications/{id}', name: 'notification_detail', methods: ['GET'])]
    #[OA\Get(
        path: '/api/notifications/{id}',
        summary: 'Get notification details',
        description: 'Returns full notification details for mobile app. Optionally updates consumer last_active_at.'
    )]
    #[OA\Parameter(name: 'id', in: 'path', schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\Response(response: 200, description: 'Notification details', content: new Model(type: NotificationDetailResponse::class))]
    #[OA\Response(response: 404, description: 'Notification not found', content: new Model(type: ErrorResponse::class))]
    #[OA\Tag(name: 'Notifications')]
    public function detail(string $id, Request $request): JsonResponse
    {
        $notification = $this->notificationRepository->find($id);
        if (!$notification) {
            return $this->response(new ErrorResponse(404, 'Notification not found', 'NotFoundError'), 404);
        }

        $consumerUuid = $request->headers->get('X-Consumer-UUID') ?? $request->query->get('consumer_uuid');
        if ($consumerUuid) {
            $consumer = $this->consumerRepository->find($consumerUuid);
            if ($consumer) {
                $consumer->setLastActiveAt(new \DateTimeImmutable());
                $this->em->flush();

                $this->bus->dispatch(new UpdateSubscriptionActivity(
                    $consumerUuid,
                    (string) $notification->getChannel()->getId(),
                ));
            }
        }

        return $this->response(NotificationDetailResponse::fromEntity($notification));
    }
}
