<?php

namespace App\Repository;

use App\Entity\Channel;
use App\Entity\Consumer;
use App\Entity\Subscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Subscription>
 */
class SubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subscription::class);
    }

    public function findActiveByConsumerAndChannel(Consumer $consumer, Channel $channel): ?Subscription
    {
        return $this->createQueryBuilder('s')
            ->where('s.consumer = :consumer')
            ->andWhere('s.channel = :channel')
            ->andWhere('s.deletedAt IS NULL')
            ->setParameter('consumer', $consumer->getId(), 'uuid')
            ->setParameter('channel', $channel->getId(), 'uuid')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByConsumerAndChannel(Consumer $consumer, Channel $channel): ?Subscription
    {
        return $this->createQueryBuilder('s')
            ->where('s.consumer = :consumer')
            ->andWhere('s.channel = :channel')
            ->setParameter('consumer', $consumer->getId(), 'uuid')
            ->setParameter('channel', $channel->getId(), 'uuid')
            ->orderBy('s.subscribedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countActiveByChannel(Channel $channel): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.channel = :channel')
            ->andWhere('s.deletedAt IS NULL')
            ->setParameter('channel', $channel->getId(), 'uuid')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countActiveByConsumer(Consumer $consumer): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.consumer = :consumer')
            ->andWhere('s.deletedAt IS NULL')
            ->setParameter('consumer', $consumer->getId(), 'uuid')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
