<?php

namespace App\Command;

use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:subscriptions:purge',
    description: 'Hard-delete subscriptions soft-deleted more than 30 days ago'
)]
class PurgeSubscriptionsCommand extends Command
{
    public function __construct(
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $threshold = new \DateTimeImmutable('-30 days');
        $count = 0;
        $batchSize = 100;

        $subscriptions = $this->subscriptionRepository->createQueryBuilder('s')
            ->where('s.deletedAt IS NOT NULL')
            ->andWhere('s.deletedAt < :threshold')
            ->setParameter('threshold', $threshold)
            ->getQuery()
            ->getResult();

        foreach ($subscriptions as $subscription) {
            $this->em->remove($subscription);
            $count++;

            if ($count % $batchSize === 0) {
                $this->em->flush();
            }
        }

        $this->em->flush();
        $output->writeln("Purged $count subscriptions");

        return Command::SUCCESS;
    }
}
