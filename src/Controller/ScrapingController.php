<?php

namespace App\Controller;

use App\Entity\Recette;
use App\Services\RecetteScraperService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ScrapingController extends AbstractController
{
    #[Route('/admin/scraping', name: 'scraping_form')]
    public function scrapingForm(): Response
    {
        return $this->render('scraping/form.html.twig');
    }

    #[Route('/admin/scraping/process', name: 'scraping_process', methods: ['POST'])]
    public function processscraping(
        Request              $request,
        RecetteScraperService $scraperService
    ): Response
    {
        $url = $request->request->get('url');

        if (!$url) {
            $this->addFlash('error', 'Veuillez fournir une URL');
            return $this->redirectToRoute('scraping_form');
        }

        $recipe = $scraperService->scrapeRecipe($url);

        if ($recipe) {
            $this->addFlash('success', 'Recette "' . $recipe->getTitle() . '" ajoutée avec succès !');
        } else {
            $this->addFlash('error', 'Impossible de récupérer la recette depuis cette URL');
        }

        return $this->redirectToRoute('scraping_form');
    }

    #[Route('/admin/recipes', name: 'recipes_list')]
    public function listRecipes(EntityManagerInterface $em): Response
    {
        $recipes = $em->getRepository(Recette::class)->findAll();

        return $this->render('scraping/list.html.twig', [
            'recipes' => $recipes
        ]);
    }


    #[Route('/recipe/{id}', name: 'recipe_show')]
    public function showRecipe(Recette $recipe): Response
    {
        return $this->render('scraping/show.html.twig', [
            'recipe' => $recipe
        ]);
    }
}
