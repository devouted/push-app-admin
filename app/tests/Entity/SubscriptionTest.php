<?php

namespace App\Tests\Entity;

use App\Entity\Channel;
use App\Entity\Consumer;
use App\Entity\Subscription;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class SubscriptionTest extends TestCase
{
    public function testConstructorSetsIdAndSubscribedAt(): void
    {
        $subscription = new Subscription();

        $this->assertInstanceOf(Uuid::class, $subscription->getId());
        $this->assertInstanceOf(\DateTimeImmutable::class, $subscription->getSubscribedAt());
        $this->assertNull($subscription->getLastActiveAt());
        $this->assertNull($subscription->getDeletedAt());
        $this->assertFalse($subscription->isDeleted());
    }

    public function testUniqueIds(): void
    {
        $a = new Subscription();
        $b = new Subscription();

        $this->assertNotEquals((string) $a->getId(), (string) $b->getId());
    }

    public function testSetConsumerAndChannel(): void
    {
        $subscription = new Subscription();
        $consumer = new Consumer();
        $channel = new Channel();

        $subscription->setConsumer($consumer);
        $subscription->setChannel($channel);

        $this->assertSame($consumer, $subscription->getConsumer());
        $this->assertSame($channel, $subscription->getChannel());
    }

    public function testSoftDelete(): void
    {
        $subscription = new Subscription();
        $this->assertFalse($subscription->isDeleted());

        $now = new \DateTimeImmutable();
        $subscription->setDeletedAt($now);
        $this->assertTrue($subscription->isDeleted());
        $this->assertSame($now, $subscription->getDeletedAt());

        $subscription->setDeletedAt(null);
        $this->assertFalse($subscription->isDeleted());
    }

    public function testLastActiveAt(): void
    {
        $subscription = new Subscription();
        $this->assertNull($subscription->getLastActiveAt());

        $now = new \DateTimeImmutable();
        $subscription->setLastActiveAt($now);
        $this->assertSame($now, $subscription->getLastActiveAt());
    }
}
