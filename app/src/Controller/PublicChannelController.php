<?php

namespace App\Controller;

use App\Dto\Response\ErrorResponse;
use App\Dto\Response\PublicChannelItemResponse;
use App\Dto\Response\PublicChannelListResponse;
use App\Dto\Response\PublicNotificationItemResponse;
use App\Dto\Response\PublicNotificationListResponse;
use App\Dto\Response\SubscriptionResponse;
use App\Entity\Channel;
use App\Entity\Consumer;
use App\Entity\Notification;
use App\Entity\Subscription;
use App\Enum\ChannelStatus;
use App\Repository\ChannelRepository;
use App\Repository\ConsumerRepository;
use App\Repository\NotificationRepository;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/public/channels')]
class PublicChannelController extends DefaultController
{
    public function __construct(
        private readonly ChannelRepository $channelRepository,
        private readonly ConsumerRepository $consumerRepository,
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly NotificationRepository $notificationRepository,
        private readonly EntityManagerInterface $em,
    ) {}

    #[Route('', name: 'public_channels_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/public/channels',
        summary: 'List public channels',
        description: 'Returns paginated list of public active channels with subscriber count'
    )]
    #[OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1))]
    #[OA\Parameter(name: 'limit', in: 'query', schema: new OA\Schema(type: 'integer', default: 20))]
    #[OA\Parameter(name: 'category', in: 'query', schema: new OA\Schema(type: 'string'), required: false)]
    #[OA\Parameter(name: 'language', in: 'query', schema: new OA\Schema(type: 'string'), required: false)]
    #[OA\Response(response: 200, description: 'Channels list', content: new Model(type: PublicChannelListResponse::class))]
    #[OA\Response(response: 401, description: 'Missing consumer', content: new Model(type: ErrorResponse::class))]
    #[OA\Tag(name: 'Public Channels')]
    public function list(Request $request): JsonResponse
    {
        $consumer = $this->resolveConsumer($request);
        if ($consumer instanceof JsonResponse) {
            return $consumer;
        }

        $page = max(1, $request->query->getInt('page', 1));
        $limit = min(100, max(1, $request->query->getInt('limit', 20)));
        $category = $request->query->get('category');
        $language = $request->query->get('language');

        $channels = $this->channelRepository->findPublicActive($category, $language, $page, $limit);
        $total = $this->channelRepository->countPublicActive($category, $language);

        $items = array_map(function (Channel $c) {
            $count = $this->subscriptionRepository->countActiveByChannel($c);
            return PublicChannelItemResponse::fromEntity($c, $count);
        }, $channels);

        return $this->response(new PublicChannelListResponse($items, $total, $page, $limit));
    }

    #[Route('/{id}/subscribe', name: 'public_channels_subscribe', methods: ['POST'])]
    #[OA\Post(
        path: '/api/public/channels/{id}/subscribe',
        summary: 'Subscribe to channel',
        description: 'Creates a subscription for the consumer to the channel'
    )]
    #[OA\Response(response: 201, description: 'Subscribed', content: new Model(type: SubscriptionResponse::class))]
    #[OA\Response(response: 401, description: 'Missing consumer', content: new Model(type: ErrorResponse::class))]
    #[OA\Response(response: 403, description: 'Max subscribers reached', content: new Model(type: ErrorResponse::class))]
    #[OA\Response(response: 404, description: 'Channel not found', content: new Model(type: ErrorResponse::class))]
    #[OA\Response(response: 409, description: 'Already subscribed', content: new Model(type: ErrorResponse::class))]
    #[OA\Tag(name: 'Public Channels')]
    public function subscribe(string $id, Request $request): JsonResponse
    {
        $consumer = $this->resolveConsumer($request);
        if ($consumer instanceof JsonResponse) {
            return $consumer;
        }

        $channel = $this->findActiveChannel($id);
        if ($channel instanceof JsonResponse) {
            return $channel;
        }

        $existing = $this->subscriptionRepository->findByConsumerAndChannel($consumer, $channel);

        if ($existing && !$existing->isDeleted()) {
            return $this->response(new ErrorResponse(409, 'Already subscribed', 'ConflictError'), 409);
        }

        if ($channel->getMaxSubscribers() !== null) {
            $count = $this->subscriptionRepository->countActiveByChannel($channel);
            if ($count >= $channel->getMaxSubscribers()) {
                return $this->response(new ErrorResponse(403, 'Max subscribers reached', 'ForbiddenError'), 403);
            }
        }

        if ($existing && $existing->isDeleted()) {
            $existing->setDeletedAt(null);
            $this->em->flush();
            return $this->response(new SubscriptionResponse((string) $existing->getId()), 201);
        }

        $subscription = new Subscription();
        $subscription->setConsumer($consumer);
        $subscription->setChannel($channel);
        $this->em->persist($subscription);
        $this->em->flush();

        return $this->response(new SubscriptionResponse((string) $subscription->getId()), 201);
    }

    #[Route('/{id}/subscribe', name: 'public_channels_unsubscribe', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/public/channels/{id}/subscribe',
        summary: 'Unsubscribe from channel',
        description: 'Soft-deletes the subscription'
    )]
    #[OA\Response(response: 204, description: 'Unsubscribed')]
    #[OA\Response(response: 401, description: 'Missing consumer', content: new Model(type: ErrorResponse::class))]
    #[OA\Response(response: 404, description: 'Subscription not found', content: new Model(type: ErrorResponse::class))]
    #[OA\Tag(name: 'Public Channels')]
    public function unsubscribe(string $id, Request $request): JsonResponse
    {
        $consumer = $this->resolveConsumer($request);
        if ($consumer instanceof JsonResponse) {
            return $consumer;
        }

        $channel = $this->channelRepository->find($id);
        if (!$channel) {
            return $this->response(new ErrorResponse(404, 'Channel not found', 'NotFoundError'), 404);
        }

        $subscription = $this->subscriptionRepository->findActiveByConsumerAndChannel($consumer, $channel);
        if (!$subscription) {
            return $this->response(new ErrorResponse(404, 'Subscription not found', 'NotFoundError'), 404);
        }

        $subscription->setDeletedAt(new \DateTimeImmutable());
        $this->em->flush();

        return new JsonResponse(null, 204);
    }

    #[Route('/{id}/notifications', name: 'public_channels_notifications', methods: ['GET'])]
    #[OA\Get(
        path: '/api/public/channels/{id}/notifications',
        summary: 'List channel notifications',
        description: 'Returns paginated notifications for a channel. Consumer must be subscribed.'
    )]
    #[OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1))]
    #[OA\Parameter(name: 'limit', in: 'query', schema: new OA\Schema(type: 'integer', default: 20))]
    #[OA\Response(response: 200, description: 'Notifications list', content: new Model(type: PublicNotificationListResponse::class))]
    #[OA\Response(response: 401, description: 'Missing consumer', content: new Model(type: ErrorResponse::class))]
    #[OA\Response(response: 403, description: 'Not subscribed', content: new Model(type: ErrorResponse::class))]
    #[OA\Response(response: 404, description: 'Channel not found', content: new Model(type: ErrorResponse::class))]
    #[OA\Tag(name: 'Public Channels')]
    public function notifications(string $id, Request $request): JsonResponse
    {
        $consumer = $this->resolveConsumer($request);
        if ($consumer instanceof JsonResponse) {
            return $consumer;
        }

        $channel = $this->channelRepository->find($id);
        if (!$channel || $channel->isDeleted()) {
            return $this->response(new ErrorResponse(404, 'Channel not found', 'NotFoundError'), 404);
        }

        $subscription = $this->subscriptionRepository->findActiveByConsumerAndChannel($consumer, $channel);
        if (!$subscription) {
            return $this->response(new ErrorResponse(403, 'Not subscribed to this channel', 'ForbiddenError'), 403);
        }

        $page = max(1, $request->query->getInt('page', 1));
        $limit = min(100, max(1, $request->query->getInt('limit', 20)));

        $notifications = $this->notificationRepository->findByChannel($channel, $page, $limit);
        $total = $this->notificationRepository->countByChannel($channel);

        $items = array_map(fn(Notification $n) => PublicNotificationItemResponse::fromEntity($n), $notifications);

        return $this->response(new PublicNotificationListResponse($items, $total, $page, $limit));
    }

    private function resolveConsumer(Request $request): Consumer|JsonResponse
    {
        $uuid = $request->headers->get('X-Consumer-UUID');
        if (!$uuid) {
            return $this->response(new ErrorResponse(401, 'Missing X-Consumer-UUID header', 'AuthenticationError'), 401);
        }

        $consumer = $this->consumerRepository->find($uuid);
        if (!$consumer || $consumer->isDeleted()) {
            return $this->response(new ErrorResponse(401, 'Consumer not found', 'AuthenticationError'), 401);
        }

        return $consumer;
    }

    private function findActiveChannel(string $id): Channel|JsonResponse
    {
        $channel = $this->channelRepository->find($id);
        if (!$channel || $channel->isDeleted() || $channel->getStatus() !== ChannelStatus::ACTIVE) {
            return $this->response(new ErrorResponse(404, 'Channel not found', 'NotFoundError'), 404);
        }

        return $channel;
    }
}
