<?php
namespace App\Controller;

use App\Repository\RecetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[\Symfony\Component\Routing\Attribute\Route('/home', name: 'app_recette_home', methods: ['GET', 'POST'])]
    public function index(RecetteRepository $recetteRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'recettes' => $recetteRepository->findAll(),
        ]);
    }
}





