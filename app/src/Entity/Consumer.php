<?php

namespace App\Entity;

use App\Repository\ConsumerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ConsumerRepository::class)]
#[ORM\Table(name: 'consumers')]
class Consumer
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column(length: 255)]
    private string $expoToken;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $deviceName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $deviceModel = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $deviceOs = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $deviceOsVersion = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastActiveAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getExpoToken(): string
    {
        return $this->expoToken;
    }

    public function setExpoToken(string $expoToken): static
    {
        $this->expoToken = $expoToken;
        return $this;
    }

    public function getDeviceName(): ?string
    {
        return $this->deviceName;
    }

    public function setDeviceName(?string $deviceName): static
    {
        $this->deviceName = $deviceName;
        return $this;
    }

    public function getDeviceModel(): ?string
    {
        return $this->deviceModel;
    }

    public function setDeviceModel(?string $deviceModel): static
    {
        $this->deviceModel = $deviceModel;
        return $this;
    }

    public function getDeviceOs(): ?string
    {
        return $this->deviceOs;
    }

    public function setDeviceOs(?string $deviceOs): static
    {
        $this->deviceOs = $deviceOs;
        return $this;
    }

    public function getDeviceOsVersion(): ?string
    {
        return $this->deviceOsVersion;
    }

    public function setDeviceOsVersion(?string $deviceOsVersion): static
    {
        $this->deviceOsVersion = $deviceOsVersion;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
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
