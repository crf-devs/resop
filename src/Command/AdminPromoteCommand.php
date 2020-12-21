<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AdminPromoteCommand extends Command
{
    protected static $defaultName = 'app:admin-promote';

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Promote an user as a super admin')
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'The user email address');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $userRepository = $this->em->getRepository(User::class);

        $email = $input->getOption('email');
        if (!\is_string($email) || empty($email)) {
            throw new \InvalidArgumentException('Bad email provided');
        }

        $user = $userRepository->findOneBy(['emailAddress' => $email]);
        if (empty($user)) {
            throw new \InvalidArgumentException('User not found');
        }

        $user->roles = array_unique(array_merge((array) $user->roles, ['ROLE_SUPER_ADMIN']));
        $this->em->flush();

        $output->writeln('User is now a super admin');

        return 0;
    }
}
