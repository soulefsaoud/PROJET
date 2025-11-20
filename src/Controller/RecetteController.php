<?php

namespace App\Controller;


use App\Entity\Recette;
use App\Form\RecetteForm;
use App\Repository\RecetteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;


# Initialisation du repository de recettes

class RecetteController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }




    #[Route('/search', name: 'recipe_search', methods: ['POST'])]
    public function search(Request $request, PaginatorInterface $paginator): JsonResponse
    {
        $ingredient = $request->get('ingredient');
        $page = $request->get('page', 1);
        $limit = 9; // Nombre de recettes par page

        // Récupérer la query (pas les résultats directement)
        $query = $this->recipeRepository->findByIngredientQuery($ingredient);

        // Paginer les résultats
        $pagination = $paginator->paginate(
            $query,
            $page,
            $limit
        );

        // Convertir en array pour JSON
        $recipes = [];
        foreach ($pagination->getItems() as $recipe) {
            $recipes[] = [
                'id' => $recipe->getId(),
                'name' => $recipe->getName(),
                'description' => $recipe->getDescription(),
                'image' => $recipe->getImage(),
                'cookingTime' => $recipe->getCookingTime(),
            ];
        }

        return $this->json([
            'success' => true,
            'recipes' => $recipes,
            'pagination' => [
                'current_page' => $pagination->getCurrentPageNumber(),
                'total_pages' => $pagination->getPageCount(),
                'total_items' => $pagination->getTotalItemCount(),
                'items_per_page' => $pagination->getItemNumberPerPage(),
                'has_previous' => $pagination->getCurrentPageNumber() > 1,
                'has_next' => $pagination->getCurrentPageNumber() < $pagination->getPageCount(),
            ]
        ]);
    }






// Dans ton contrôleur - Modification de la méthode searchRecipes
    #[Route('/api/search-recipes', name: 'api_search_recipes', methods: ['GET', 'POST'])]
    public function searchRecipes(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?: [];
        $ingredients = $data['ingredients'] ?? [];
        $regime = $data['regime'] ?? null;
        $page = max(1, $data['page'] ?? 1);
        $limit = min(50, max(1, $data['limit'] ?? 12));

        if (empty($ingredients)) {
            return new JsonResponse(['error' => 'Le tableau des ingrédients est vide.']);
        }

        $results = $this->searchByIngredients($ingredients, $regime, $page, $limit);
        return new JsonResponse($results);
    }

    public function searchByIngredients(array $ingredients, ?string $regime, int $page = 1, int $limit = 12): array
    {
        // Nettoyage rapide des ingrédients
        $ingredientNames = array_filter(array_map('trim', $ingredients));
        if (empty($ingredientNames)) {
            return ['error' => 'Aucun ingrédient valide trouvé.'];
        }

        $conn = $this->entityManager->getConnection();

        // Préparation des paramètres une seule fois
        $escapedIngredients = array_map(fn($i) => $conn->quote(strtolower($i)), $ingredientNames);
        $ingredientNamesString = implode(',', $escapedIngredients);
        $offset = ($page - 1) * $limit;

        try {
            // Requête unique avec COUNT() OVER() pour éviter 2 requêtes
            $sql = "
        SELECT
            r.id, r.nom, r.instructions, r.temps_preparation, r.temps_cuisson,
            r.descriptions, r.difficulte, r.date_creation, r.nombre_de_portions,
            matched_ingredients.match_count,
            all_ingredients.total_ingredients,
            matched_ingredients.matched_names,
            COUNT(*) OVER() as total_results
        FROM Recette r
        INNER JOIN (
            SELECT
                ri.recette_id,
                COUNT(DISTINCT i.id) as match_count,
                GROUP_CONCAT(DISTINCT i.nom) as matched_names
            FROM Ingredient_Recette ri
            INNER JOIN Ingredient i ON ri.ingredient_id = i.id
            WHERE LOWER(TRIM(i.nom)) IN ($ingredientNamesString)
            GROUP BY ri.recette_id
        ) matched_ingredients ON r.id = matched_ingredients.recette_id
        LEFT JOIN (
            SELECT
                recette_id,
                COUNT(DISTINCT ingredient_id) as total_ingredients
            FROM Ingredient_Recette
            GROUP BY recette_id
        ) all_ingredients ON r.id = all_ingredients.recette_id
        " . ($regime ? "
        INNER JOIN Regime_Recette rr ON r.id = rr.recette_id
        INNER JOIN Regime reg ON rr.regime_id = reg.id AND reg.nom = :regime
        " : "") . "
        ORDER BY matched_ingredients.match_count DESC, all_ingredients.total_ingredients ASC
        LIMIT :limit OFFSET :offset
        ";

            $stmt = $conn->prepare($sql);
            if ($regime) {
                $stmt->bindValue('regime', $regime);
            }
            $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
            $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);

            $results = $stmt->executeQuery()->fetchAllAssociative();

            if (empty($results)) {
                return [
                    'recettes' => [],
                    'pagination' => [
                        'currentPage' => $page,
                        'totalPages' => 0,
                        'totalResults' => 0,
                        'limit' => $limit,
                        'hasNext' => false,
                        'hasPrevious' => false
                    ]
                ];
            }

            $totalResults = (int)$results[0]['total_results'];
            $totalPages = (int)ceil($totalResults / $limit);

            // Construction rapide du résultat
            $recettes = array_map(function($r) {
                return [
                    'id' => $r['id'],
                    'nom' => $r['nom'] ?: 'Nom inconnu',
                    'instructions' => $r['instructions'] ?: '',
                    'descriptions' => $r['descriptions'] ?: '',
                    'tempsPreparation' => $r['temps_preparation'] ?: '10',
                    'tempsCuisson' => $r['temps_cuisson'] ?: '10',
                    'difficulte' => $r['difficulte'] ?: 'facile',
                    'date_creation' => $r['date_creation'],
                    'nombre_de_portion' => $r['nombre_de_portions'] ?: '10',
                    'matchCount' => (int)$r['match_count'],
                    'totalIngredients' => (int)$r['total_ingredients'],
                    'matchedIngredientNames' => $r['matched_names'] ? explode(',', $r['matched_names']) : [],
                ];
            }, $results);

            return [
                'recettes' => $recettes,
                'pagination' => [
                    'currentPage' => $page,
                    'totalPages' => $totalPages,
                    'totalResults' => $totalResults,
                    'limit' => $limit,
                    'hasNext' => $page < $totalPages,
                    'hasPrevious' => $page > 1
                ]
            ];

        } catch (\Exception $e) {
            return ['error' => 'Une erreur est survenue : ' . $e->getMessage()];
        }
    }
    /**
     * @Route("/api/recettes", name="api_recettes", methods={"GET", "POST"})
     */
    #[Route('/api/recettes', name: 'api_recettes', methods: ['GET', 'POST'])]
    public function getRecipes(ManagerRegistry $doctrine): JsonResponse
    {
        $recipes = $doctrine->getRepository(Recette::class)->findAll();
        $data = [];
        foreach ($recipes as $recipe) {
            $data[] = [
                'id' => $recipe->getId(),
                'nom' => $recipe->getNom(),
                'instructions' => $recipe->getInstructions(),
                'descriptions' => $recipe->getDescription(),
                'ingredients' => array_map(function($ingredient) {
                    return [
                        'quantite' => $ingredient->getQuantite(),
                        'unite_mesure' => $ingredient->getUniteMesure()
                    ];
                }, $recipe->getIngredientRecettes()->toArray())
            ];
        }
        return new JsonResponse($data);
    }


    #[Route('/api/recipesSaved', name: 'api_recipes_saved', methods: ['POST','GET','GET'])]
    public function saveRecipes(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON data: ' . json_last_error_msg());
            }

            if (!is_array($data)) {
                throw new \Exception('Data is not an array');
            }

            foreach ($data as $item) {
                if (!isset($item['nom'])) {
                    throw new \Exception("La clé 'nom' est manquante dans l'un des éléments.");
                }

                $recette = new Recette();
                $recette->setNom($item['nom']);
                $recette->setInstructions($item['instructions'] ?? '');
                $recette->setTempsPreparation($item['tempsPreparation'] ?? 0);
                $recette->setTempsCuisson($item['tempsCuisson'] ?? 0);
                $recette->setDifficulte($item['difficulte'] ?? '');
                $recette->setDateCreation(new \DateTime($item['date_creation'] ?? 'now'));
                $recette->setNombreDePortions($item['nombre_de_portions'] ?? 0);

                $this->entityManager->persist($recette);
            }

            $this->entityManager->flush();

            return new JsonResponse(['message' => 'Données enregistrées avec succès']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Une erreur est survenue : ' . $e->getMessage()], 500);
        }
    }




    #[Route('/recettes', name: 'api_recette_index', methods: ['GET'])]
    public function index(RecetteRepository $recetteRepository): Response
    {
        return $this->render('recette/index.html.twig', [
            'recettes' => $recetteRepository->findAll(),
        ]);
    }







    #[Route('/new', name: 'app_recette_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $recette = new Recette();
        $form = $this->createForm(RecetteForm::class, $recette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($recette);
            $entityManager->flush();

            return $this->redirectToRoute('api_recette_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('recette/new.html.twig', [
            'recette' => $recette,
            'form' => $form->createView(),
        ]);
    }



#[Route('/api/recette/{id}', name: 'api_recette_show', methods: ['GET'])]
        public function show(Recette $recette): Response
        {
            return $this->render('recette/show.html.twig', [
                'recette' => $recette,
            ]);
        }


        #[Route('/{id}/edit', name: 'api_recette_edit', methods: ['GET', 'POST','GET'])]
        public function edit(Request $request, Recette $recette, EntityManagerInterface $entityManager): Response
        {
            $form = $this->createForm(RecetteForm::class, $recette);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->flush();

                return $this->redirectToRoute('api_recette_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('recette/edit.html.twig', [
                'recette' => $recette,
                'form' => $form,
            ]);
        }

        #[Route('/{id}/delete', name: 'api_recette_delete', methods: ['POST','GET'])]
        public function delete(Request $request, Recette $recette, EntityManagerInterface $entityManager): Response
        {
            if ($this->isCsrfTokenValid('delete' . $recette->getId(), $request->getPayload()->getString('_token'))) {
                $entityManager->remove($recette);
                $entityManager->flush();
            }

            return $this->redirectToRoute('api_recette_index', [], Response::HTTP_SEE_OTHER);
        }



       /**
        * Valide et nettoie les IDs d'ingrédients
        */
        private
        function validateIngredientIds(array $ingredientIds): array
        {
            $validIds = [];

            foreach ($ingredientIds as $id) {
                // Nettoyer et valider l'ID
                $cleanId = filter_var($id, FILTER_VALIDATE_INT);

                if ($cleanId !== false && $cleanId > 0) {
                    $validIds[] = $cleanId;
                }
            }

            // Supprimer les doublons
            return array_unique($validIds);
        }

        /**
         * Génère des statistiques de recherche
         */
        private
        function getSearchStats(array $recettes): array
        {
            if (empty($recettes)) {
                return [];
            }

            $stats = [
                'total' => count($recettes),
                'avgScore' => 0,
                'bestMatch' => null,
                'scoreDistribution' => [
                    'excellent' => 0, // >= 90%
                    'good' => 0,      // 70-89%
                    'fair' => 0,      // 50-69%
                    'poor' => 0       // < 50%
                ]
            ];

            $totalScore = 0;
            $bestScore = 0;

            foreach ($recettes as $result) {
                $score = $result['score'] ?? 0;
                $totalScore += $score;

                // Meilleur match
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $stats['bestMatch'] = $result;
                }

                // Distribution des scores
                if ($score >= 90) {
                    $stats['scoreDistribution']['excellent']++;
                } elseif ($score >= 70) {
                    $stats['scoreDistribution']['good']++;
                } elseif ($score >= 50) {
                    $stats['scoreDistribution']['fair']++;
                } else {
                    $stats['scoreDistribution']['poor']++;
                }
            }

            $stats['avgScore'] = round($totalScore / count($recettes), 1);

            return $stats;
        }

    public function showRecipe(Recette $recipe): Response
    {
        return $this->render('recipe/show.html.twig', [
            'recette' => $recipe,
        ]);
    }


}
