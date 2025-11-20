<?php
// src/Controller/ResetPasswordController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ResetPasswordController extends AbstractController
{
    #[Route('/reset-password', name: 'app_forgot_password_request')]
    public function request(): Response
    {
        // Logique pour la demande de réinitialisation
        return new Response('Page de réinitialisation à venir...');
    }
}
