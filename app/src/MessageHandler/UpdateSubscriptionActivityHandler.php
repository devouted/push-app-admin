<?php

namespace App\MessageHandler;

use App\Message\UpdateSubscriptionActivity;
use App\Repository\ConsumerRepository;
use App\Repository\ChannelRepository;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateSubscriptionActivityHandler
{
    public function __construct(
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly ConsumerRepository $consumerRepository,
        private readonly ChannelRepository $channelRepository,
        private readonly EntityManagerInterface $em,
    ) {}

    public function __invoke(UpdateSubscriptionActivity $message): void
    {
        $consumer = $this->consumerRepository->find($message->consumerId);
        $channel = $this->channelRepository->find($message->channelId);

        if (!$consumer || !$channel) {
            return;
        }

        $subscription = $this->subscriptionRepository->findActiveByConsumerAndChannel($consumer, $channel);
        if (!$subscription) {
            return;
        }

        $subscription->setLastActiveAt(new \DateTimeImmutable());
        $this->em->flush();
    }
}
