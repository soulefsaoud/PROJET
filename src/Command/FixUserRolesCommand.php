<?php
// Create this file: src/Command/FixUserRolesCommand.php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:fix-user-roles',
    description: 'Fix user roles data in database to ensure proper JSON format',
)]
class FixUserRolesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Fixing User Roles Data');

        // Get all users
        $users = $this->entityManager->getRepository(User::class)->findAll();
        $fixedCount = 0;

        foreach ($users as $user) {
            $currentRoles = $user->getRoles(); // This will handle the conversion

            // Get the raw roles data to see if it needs fixing
            $reflection = new \ReflectionClass($user);
            $rolesProperty = $reflection->getProperty('roles');
            $rolesProperty->setAccessible(true);
            $rawRoles = $rolesProperty->getValue($user);

            // If the raw data is not an array, we need to fix it
            if (!is_array($rawRoles)) {
                $io->writeln(sprintf('Fixing roles for user %s (ID: %d)', $user->getEmail(), $user->getId()));
                $io->writeln(sprintf('  Old data: %s (%s)', var_export($rawRoles, true), gettype($rawRoles)));

                // Set the properly converted roles
                $user->setRoles($currentRoles);
                $fixedCount++;

                $io->writeln(sprintf('  New data: %s', json_encode($currentRoles)));
            }
        }

        if ($fixedCount > 0) {
            $this->entityManager->flush();
            $io->success(sprintf('Fixed roles data for %d users.', $fixedCount));
        } else {
            $io->success('No users needed role data fixing.');
        }

        return Command::SUCCESS;
    }
}
