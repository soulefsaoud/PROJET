<?php
namespace App\Controller;

use App\Repository\RecetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Recette; // Assurez-vous d'importer votre entité Recette
use Doctrine\Persistence\ManagerRegistry;
#[Route('/'  , name: 'app_home', methods: ['GET', 'POST'])]
class HomeController extends AbstractController
{


    #[Route('/', name: 'app_home')]
    public function index(ManagerRegistry $doctrine): Response
    {
        // Récupérer les recettes depuis la base de données
        $recettes = $doctrine->getRepository(Recette::class)->findAll();

        // Passer les recettes au template
        return $this->render('home/index.html.twig', [
            'recettes' => $recettes,
        ]);
    }
}


