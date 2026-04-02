<?php

namespace App\Command;

use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:subscriptions:deactivate',
    description: 'Soft-delete subscriptions inactive longer than channel inactivity_timeout_days'
)]
class DeactivateSubscriptionsCommand extends Command
{
    public function __construct(
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $now = new \DateTimeImmutable();
        $count = 0;
        $batchSize = 100;

        $subscriptions = $this->subscriptionRepository->createQueryBuilder('s')
            ->join('s.channel', 'c')
            ->where('s.deletedAt IS NULL')
            ->andWhere('s.lastActiveAt IS NOT NULL')
            ->getQuery()
            ->getResult();

        foreach ($subscriptions as $subscription) {
            $timeoutDays = $subscription->getChannel()->getInactivityTimeoutDays();
            $threshold = $now->modify("-{$timeoutDays} days");

            if ($subscription->getLastActiveAt() < $threshold) {
                $subscription->setDeletedAt($now);
                $count++;

                if ($count % $batchSize === 0) {
                    $this->em->flush();
                }
            }
        }

        $this->em->flush();
        $output->writeln("Deactivated $count subscriptions");

        return Command::SUCCESS;
    }
}
