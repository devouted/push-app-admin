<?php

namespace App\Repository;

use App\Entity\Channel;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Channel>
 */
class ChannelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Channel::class);
    }

    /**
     * @return Channel[]
     */
    public function findByOwner(User $owner, int $page = 1, int $limit = 20): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.owner = :owner')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('owner', $owner)
            ->orderBy('c.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countByOwner(User $owner): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.owner = :owner')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('owner', $owner)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return Channel[]
     */
    public function findPublicActive(?string $category = null, ?string $language = null, int $page = 1, int $limit = 20): array
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.status = :status')
            ->andWhere('c.isPublic = true')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('status', 'active')
            ->orderBy('c.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        if ($category !== null) {
            $qb->andWhere('c.category = :category')->setParameter('category', $category);
        }
        if ($language !== null) {
            $qb->andWhere('c.language = :language')->setParameter('language', $language);
        }

        return $qb->getQuery()->getResult();
    }

    public function countPublicActive(?string $category = null, ?string $language = null): int
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.status = :status')
            ->andWhere('c.isPublic = true')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('status', 'active');

        if ($category !== null) {
            $qb->andWhere('c.category = :category')->setParameter('category', $category);
        }
        if ($language !== null) {
            $qb->andWhere('c.language = :language')->setParameter('language', $language);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
