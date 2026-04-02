<?php

namespace App\Tests\MessageHandler;

use App\Entity\Channel;
use App\Entity\Consumer;
use App\Entity\Subscription;
use App\Message\UpdateSubscriptionActivity;
use App\MessageHandler\UpdateSubscriptionActivityHandler;
use App\Repository\ChannelRepository;
use App\Repository\ConsumerRepository;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class UpdateSubscriptionActivityHandlerTest extends TestCase
{
    public function testUpdatesLastActiveAtWhenSubscriptionExists(): void
    {
        $consumer = new Consumer();
        $channel = new Channel();
        $subscription = new Subscription();
        $subscription->setConsumer($consumer);
        $subscription->setChannel($channel);

        $consumerRepo = $this->createMock(ConsumerRepository::class);
        $consumerRepo->method('find')->willReturn($consumer);

        $channelRepo = $this->createMock(ChannelRepository::class);
        $channelRepo->method('find')->willReturn($channel);

        $subRepo = $this->createMock(SubscriptionRepository::class);
        $subRepo->method('findActiveByConsumerAndChannel')->willReturn($subscription);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');

        $handler = new UpdateSubscriptionActivityHandler($subRepo, $consumerRepo, $channelRepo, $em);
        $handler(new UpdateSubscriptionActivity('consumer-id', 'channel-id'));

        $this->assertNotNull($subscription->getLastActiveAt());
    }

    public function testDoesNothingWhenNoSubscription(): void
    {
        $consumer = new Consumer();
        $channel = new Channel();

        $consumerRepo = $this->createMock(ConsumerRepository::class);
        $consumerRepo->method('find')->willReturn($consumer);

        $channelRepo = $this->createMock(ChannelRepository::class);
        $channelRepo->method('find')->willReturn($channel);

        $subRepo = $this->createMock(SubscriptionRepository::class);
        $subRepo->method('findActiveByConsumerAndChannel')->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('flush');

        $handler = new UpdateSubscriptionActivityHandler($subRepo, $consumerRepo, $channelRepo, $em);
        $handler(new UpdateSubscriptionActivity('consumer-id', 'channel-id'));
    }

    public function testDoesNothingWhenConsumerNotFound(): void
    {
        $consumerRepo = $this->createMock(ConsumerRepository::class);
        $consumerRepo->method('find')->willReturn(null);

        $channelRepo = $this->createMock(ChannelRepository::class);
        $subRepo = $this->createMock(SubscriptionRepository::class);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('flush');

        $handler = new UpdateSubscriptionActivityHandler($subRepo, $consumerRepo, $channelRepo, $em);
        $handler(new UpdateSubscriptionActivity('bad-id', 'channel-id'));
    }

    public function testDoesNothingWhenChannelNotFound(): void
    {
        $consumer = new Consumer();

        $consumerRepo = $this->createMock(ConsumerRepository::class);
        $consumerRepo->method('find')->willReturn($consumer);

        $channelRepo = $this->createMock(ChannelRepository::class);
        $channelRepo->method('find')->willReturn(null);

        $subRepo = $this->createMock(SubscriptionRepository::class);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('flush');

        $handler = new UpdateSubscriptionActivityHandler($subRepo, $consumerRepo, $channelRepo, $em);
        $handler(new UpdateSubscriptionActivity('consumer-id', 'bad-id'));
    }
}
