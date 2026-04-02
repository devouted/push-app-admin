<?php

namespace App\Repository;

use App\Entity\Channel;
use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * @return Notification[]
     */
    public function findByChannel(Channel $channel, int $page = 1, int $limit = 20): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.channel = :channel')
            ->andWhere('n.isTest = false')
            ->setParameter('channel', $channel->getId(), 'uuid')
            ->orderBy('n.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countByChannel(Channel $channel): int
    {
        return (int) $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->where('n.channel = :channel')
            ->andWhere('n.isTest = false')
            ->setParameter('channel', $channel->getId(), 'uuid')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
