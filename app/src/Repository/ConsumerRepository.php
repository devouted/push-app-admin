<?php

namespace App\Repository;

use App\Entity\Consumer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ConsumerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Consumer::class);
    }

    public function findByExpoToken(string $expoToken): ?Consumer
    {
        return $this->findOneBy(['expoToken' => $expoToken]);
    }

    /**
     * @param string[] $tokens
     * @return Consumer[]
     */
    public function findByExpoTokens(array $tokens): array
    {
        if (empty($tokens)) {
            return [];
        }

        return $this->createQueryBuilder('c')
            ->where('c.expoToken IN (:tokens)')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('tokens', $tokens)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Consumer[]
     */
    public function findAllAdmin(int $page = 1, int $limit = 20): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.deletedAt IS NULL')
            ->orderBy('c.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countAllAdmin(): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.deletedAt IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
