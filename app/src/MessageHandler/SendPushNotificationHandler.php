<?php

namespace App\MessageHandler;

use App\Message\SendPushNotification;
use App\Repository\ConsumerRepository;
use App\Repository\NotificationRepository;
use App\Service\ExpoPushService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendPushNotificationHandler
{
    public function __construct(
        private readonly NotificationRepository $notificationRepository,
        private readonly ConsumerRepository $consumerRepository,
        private readonly ExpoPushService $expoPushService,
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(SendPushNotification $message): void
    {
        $notification = $this->notificationRepository->find($message->notificationId);
        if (!$notification) {
            $this->logger->error('Notification not found for push', ['id' => $message->notificationId]);
            return;
        }

        // TODO: Get subscriber expo tokens when Subscription entity exists (task #2433)
        $expoTokens = [];

        if (empty($expoTokens)) {
            $this->logger->info('No subscribers for channel', [
                'notification_id' => $message->notificationId,
                'channel_id' => (string) $notification->getChannel()->getId(),
            ]);
            return;
        }

        $result = $this->expoPushService->sendPush(
            $expoTokens,
            $notification->getTitle(),
            $notification->getBody(),
            (string) $notification->getId(),
            $notification->getImageUrl(),
        );

        if (!empty($result['errors'])) {
            $consumers = $this->consumerRepository->findByExpoTokens($result['errors']);
            foreach ($consumers as $consumer) {
                $consumer->setDeletedAt(new \DateTimeImmutable());
            }
            $this->em->flush();

            $this->logger->info('Soft-deleted consumers with invalid tokens', [
                'count' => count($consumers),
            ]);
        }

        $this->logger->info('Push notification sent', [
            'notification_id' => $message->notificationId,
            'sent' => $result['sent'],
            'device_not_registered' => count($result['errors']),
        ]);
    }
}
