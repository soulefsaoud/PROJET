<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Entity\Ingredient;
use App\Entity\Recette;
use App\Services\RecipeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class RecetteController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private RecipeService $recipeService;

    public function __construct(EntityManagerInterface $entityManager, RecipeService $recipeService = null)
    {
        $this->entityManager = $entityManager;
        $this->recipeService = $recipeService;
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // Récupération des tags
        $tags = $this->entityManager->getRepository(Tag::class)->findAll();

        // Récupération des ingrédients
        $ingredients = $this->entityManager->getRepository(Ingredient::class)->findAll();

        // Récupération des recettes (optionnel)
        $recipes = $this->entityManager->getRepository(Recipe::class)->findAll();

        return $this->render('home/index.html.twig', [
            'tags' => $tags,
            'ingredients' => $ingredients,
            'recipes' => $recipes,
        ]);
    }

    #[Route('/recipes/by-tag/{id}', name: 'recipes_by_tag', methods: ['GET'])]
    public function recipesByTag(int $id): JsonResponse
    {
        $tag = $this->entityManager->getRepository(Tag::class)->find($id);

        if (!$tag) {
            return new JsonResponse(['error' => 'Tag non trouvé'], 404);
        }

        // Si le service n'est pas disponible, requête directe
        if ($this->recipeService) {
            $recipes = $this->recipeService->findRecipesByTag($tag);
        } else {
            $recipes = $this->entityManager
                ->getRepository(Recipe::class)
                ->createQueryBuilder('r')
                ->innerJoin('r.tags', 't')
                ->where('t.id = :tagId')
                ->setParameter('tagId', $tag->getId())
                ->getQuery()
                ->getResult();
        }

        $data = [];
        foreach ($recipes as $recipe) {
            $data[] = [
                'id' => $recipe->getId(),
                'name' => $recipe->getName(),
                'description' => $recipe->getDescription(),
            ];
        }

        return new JsonResponse([
            'tag' => $tag->getName(),
            'recipes' => $data
        ]);
    }

    #[Route('/recipes/by-ingredient/{id}', name: 'recipes_by_ingredient', methods: ['GET'])]
    public function recipesByIngredient(int $id): JsonResponse
    {
        $ingredient = $this->entityManager->getRepository(Ingredient::class)->find($id);

        if (!$ingredient) {
            return new JsonResponse(['error' => 'Ingrédient non trouvé'], 404);
        }

        // Si le service n'est pas disponible, requête directe
        if ($this->recipeService) {
            $recipes = $this->recipeService->findRecipesByIngredient($ingredient);
        } else {
            $recipes = $this->entityManager
                ->getRepository(Recipe::class)
                ->createQueryBuilder('r')
                ->innerJoin('r.ingredients', 'i')
                ->where('i.id = :ingredientId')
                ->setParameter('ingredientId', $ingredient->getId())
                ->getQuery()
                ->getResult();
        }

        $data = [];
        foreach ($recipes as $recipe) {
            $data[] = [
                'id' => $recipe->getId(),
                'name' => $recipe->getName(),
                'description' => $recipe->getDescription(),
            ];
        }

        return new JsonResponse([
            'ingredient' => $ingredient->getName(),
            'recipes' => $data
        ]);
    }

    #[Route('/recipes/search-ingredient', name: 'search_by_ingredient', methods: ['POST'])]
    public function searchByIngredient(Request $request): JsonResponse
    {
        $ingredientName = $request->request->get('ingredient');

        if (!$ingredientName) {
            return new JsonResponse(['error' => 'Nom d\'ingrédient requis'], 400);
        }

        // Si le service n'est pas disponible, requête directe
        if ($this->recipeService) {
            $recipes = $this->recipeService->searchRecipesByIngredientName($ingredientName);
        } else {
            $recipes = $this->entityManager
                ->getRepository(Recipe::class)
                ->createQueryBuilder('r')
                ->innerJoin('r.ingredients', 'i')
                ->where('i.name LIKE :ingredientName')
                ->setParameter('ingredientName', '%' . $ingredientName . '%')
                ->getQuery()
                ->getResult();
        }

        $data = [];
        foreach ($recipes as $recipe) {
            $data[] = [
                'id' => $recipe->getId(),
                'name' => $recipe->getName(),
                'description' => $recipe->getDescription(),
            ];
        }

        return new JsonResponse([
            'search' => $ingredientName,
            'recipes' => $data
        ]);
    }

    #[Route('/api/tags', name: 'api_tags', methods: ['GET'])]
    public function getTags(): JsonResponse
    {
        $tags = $this->entityManager->getRepository(Tag::class)->findAll();

        $data = [];
        foreach ($tags as $tag) {
            $data[] = [
                'id' => $tag->getId(),
                'name' => $tag->getName(),
                'color' => $tag->getColor(),
                'recipeCount' => $tag->getRecipeCount()
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/api/ingredients', name: 'api_ingredients', methods: ['GET'])]
    public function getIngredients(): JsonResponse
    {
        $ingredients = $this->entityManager->getRepository(Ingredient::class)->findAll();

        $data = [];
        foreach ($ingredients as $ingredient) {
            $data[] = [
                'id' => $ingredient->getId(),
                'name' => $ingredient->getName(),
                'category' => $ingredient->getCategory(),
                'recipeCount' => $ingredient->getRecipeCount()
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/debug', name: 'debug_data')]
    public function debug(): Response
    {
        $tags = $this->entityManager->getRepository(Tag::class)->findAll();
        $ingredients = $this->entityManager->getRepository(Ingredient::class)->findAll();
        $recipes = $this->entityManager->getRepository(Recipe::class)->findAll();

        return new Response(
            '<h1>Debug Info</h1>' .
            '<h2>Tags (' . count($tags) . ')</h2><pre>' . print_r($tags, true) . '</pre>' .
            '<h2>Ingredients (' . count($ingredients) . ')</h2><pre>' . print_r($ingredients, true) . '</pre>' .
            '<h2>Recipes (' . count($recipes) . ')</h2><pre>' . print_r($recipes, true) . '</pre>'
        );
    }
}
