<?php

namespace App\Entity;

use App\Repository\SubscriptionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: SubscriptionRepository::class)]
#[ORM\Table(name: 'subscriptions')]
#[ORM\UniqueConstraint(columns: ['consumer_id', 'channel_id'])]
class Subscription
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: Consumer::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Consumer $consumer;

    #[ORM\ManyToOne(targetEntity: Channel::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Channel $channel;

    #[ORM\Column]
    private \DateTimeImmutable $subscribedAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastActiveAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->subscribedAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getConsumer(): Consumer
    {
        return $this->consumer;
    }

    public function setConsumer(Consumer $consumer): static
    {
        $this->consumer = $consumer;
        return $this;
    }

    public function getChannel(): Channel
    {
        return $this->channel;
    }

    public function setChannel(Channel $channel): static
    {
        $this->channel = $channel;
        return $this;
    }

    public function getSubscribedAt(): \DateTimeImmutable
    {
        return $this->subscribedAt;
    }

    public function getLastActiveAt(): ?\DateTimeImmutable
    {
        return $this->lastActiveAt;
    }

    public function setLastActiveAt(?\DateTimeImmutable $lastActiveAt): static
    {
        $this->lastActiveAt = $lastActiveAt;
        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeImmutable $deletedAt): static
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }
}
