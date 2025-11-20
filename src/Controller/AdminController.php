<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard', methods: ['GET', 'POST'])]
    public function dashboard(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        $totalUsers = count($users);
        $adminUsers = array_filter($users, fn($user) => $user->isAdmin());

        return $this->render('admin/dashboard.html.twig', [
            'total_users' => $totalUsers,
            'admin_users' => count($adminUsers),
            'recent_users' => array_slice($users, -5),
        ]);
    }

    #[Route('/users', name: 'admin_users')]
    public function users(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/users/{id}/promote', name: 'admin_promote_user')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function promoteUser(
        User $user,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$user->hasRole(User::ROLE_ADMIN)) {
            $user->addRole(User::ROLE_ADMIN);
            $entityManager->flush();

            $this->addFlash('success',
                sprintf('%s %s a été promu administrateur.',
                    $user->getFirstName(),
                    $user->getLastName()
                )
            );
        } else {
            $this->addFlash('warning', 'Cet utilisateur est déjà administrateur.');
        }

        return $this->redirectToRoute('admin_users');
    }

    #[Route('/users/{id}/demote', name: 'admin_demote_user')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function demoteUser(
        User $user,
        EntityManagerInterface $entityManager
    ): Response {
        if ($user->hasRole(User::ROLE_ADMIN)) {
            $user->removeRole(User::ROLE_ADMIN);
            $entityManager->flush();

            $this->addFlash('success',
                sprintf('%s %s n\'est plus administrateur.',
                    $user->getFirstName(),
                    $user->getLastName()
                )
            );
        } else {
            $this->addFlash('warning', 'Cet utilisateur n\'est pas administrateur.');
        }

        return $this->redirectToRoute('admin_users');
    }
}
