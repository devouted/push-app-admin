<?php

namespace App\Entity;

use App\Enum\ChannelStatus;
use App\Repository\ChannelRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ChannelRepository::class)]
#[ORM\Table(name: 'channels')]
#[ORM\HasLifecycleCallbacks]
class Channel
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $owner;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $category = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $icon = null;

    #[ORM\Column(length: 5, options: ['default' => 'pl'])]
    private string $language = 'pl';

    #[ORM\Column(length: 20, enumType: ChannelStatus::class, options: ['default' => 'active'])]
    private ChannelStatus $status = ChannelStatus::ACTIVE;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $blockedReason = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $isPublic = true;

    #[ORM\Column(nullable: true)]
    private ?int $maxSubscribers = null;

    #[ORM\Column(options: ['default' => 7])]
    private int $inactivityTimeoutDays = 7;

    #[ORM\Column(length: 64, unique: true)]
    private string $apiKey;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->apiKey = bin2hex(random_bytes(32));
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): static
    {
        $this->owner = $owner;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;
        return $this;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): static
    {
        $this->language = $language;
        return $this;
    }

    public function getStatus(): ChannelStatus
    {
        return $this->status;
    }

    public function setStatus(ChannelStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getBlockedReason(): ?string
    {
        return $this->blockedReason;
    }

    public function setBlockedReason(?string $blockedReason): static
    {
        $this->blockedReason = $blockedReason;
        return $this;
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): static
    {
        $this->isPublic = $isPublic;
        return $this;
    }

    public function getMaxSubscribers(): ?int
    {
        return $this->maxSubscribers;
    }

    public function setMaxSubscribers(?int $maxSubscribers): static
    {
        $this->maxSubscribers = $maxSubscribers;
        return $this;
    }

    public function getInactivityTimeoutDays(): int
    {
        return $this->inactivityTimeoutDays;
    }

    public function setInactivityTimeoutDays(int $inactivityTimeoutDays): static
    {
        $this->inactivityTimeoutDays = $inactivityTimeoutDays;
        return $this;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function regenerateApiKey(): static
    {
        $this->apiKey = bin2hex(random_bytes(32));
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
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
