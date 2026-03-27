<?php

namespace App\Command;

use App\Dto\Request\CreateUserRequest;
use App\Entity\User;
use App\Enum\UserRole;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:create-test-user',
    description: 'Create a test user for API testing'
)]
class CreateTestUserCommand extends Command
{
    public function __construct(
        readonly private EntityManagerInterface $entityManager,
        readonly private UserPasswordHasherInterface $passwordHasher,
        readonly private ValidatorInterface $validator,
        readonly private UserRepository $userRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addArgument('password', InputArgument::REQUIRED, 'User password')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'Grant ROLE_ADMIN');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dto = new CreateUserRequest();
        $dto->email = $input->getArgument('email');
        $dto->password = $input->getArgument('password');
        $dto->roles = $input->getOption('admin') ? [UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value] : [UserRole::ROLE_USER->value];

        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $output->writeln('<error>' . $violation->getPropertyPath() . ': ' . $violation->getMessage() . '</error>');
            }
            return Command::FAILURE;
        }

        if ($this->userRepository->findOneBy(['email' => $dto->email])) {
            $output->writeln('<error>User with this email already exists.</error>');
            return Command::FAILURE;
        }

        $user = new User();
        $user->setEmail($dto->email);
        $user->setRoles($dto->roles);
        $user->setPassword($this->passwordHasher->hashPassword($user, $dto->password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln("User created: [{$user->getId()}] {$dto->email}");
        $output->writeln('Roles: ' . implode(', ', $dto->roles));

        return Command::SUCCESS;
    }
}
