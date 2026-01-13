<?php

namespace App\Services;

use App\Entity\Recette;
use App\Entity\Ingredient;
use Doctrine\ORM\EntityManagerInterface;

            class RecetteSearchService
            {
                private EntityManagerInterface $entityManager;
                public function __construct(EntityManagerInterface $entityManager)
                {
                    $this->entityManager = $entityManager;
                }

                /**
                 * Recherche des recettes basées sur les ingrédients fournis
                 *
                 * @param array $ingredients Tableau d'objets Ingredient ou de noms d'ingrédients
                 * @return array Tableau de résultats avec score de correspondance
                 */
                public function searchByIngredients(array $ingredients): array
                {
                    if (empty($ingredients)) {
                        echo "Le tableau des ingrédients est vide.";
                        return [];
                    }

                    $ingredientNames = $this->extractIngredientNames($ingredients);

                    if (empty($ingredientNames)) {
                        echo "Aucun ingrédient valide trouvé.";
                        return [];
                    }

                    $escapedIngredients = array_map(function($ingredient) {
                        return $this->entityManager->getConnection()->quote(strtolower(trim($ingredient)));
                    }, $ingredientNames);

                    $ingredientNamesString = implode(',', $escapedIngredients);

                    try {
                        $sql = "
            SELECT
                r.id,
                r.nom,
                r.instructions,
                r.temps_preparation,
                r.temps_cuisson,
                r.difficulte,
                r.date_creation,
                r.nombre_de_portions,
                COUNT(DISTINCT matched_i.id) AS matchCount,
                COUNT(DISTINCT all_i.id) AS totalIngredients,
                GROUP_CONCAT(DISTINCT matched_i.nom) AS matchedIngredientNames
            FROM
                Recette r
            LEFT JOIN
                Ingredient_Recette ri ON r.id = ri.recette_id
            LEFT JOIN
                Ingredient all_i ON all_i.id = ri.ingredient_id
            LEFT JOIN
                Ingredient_Recette ri2 ON r.id = ri2.recette_id
            LEFT JOIN
                Ingredient matched_i ON matched_i.id = ri2.ingredient_id
                AND LOWER(TRIM(matched_i.nom)) IN ($ingredientNamesString)
            GROUP BY
                r.id
            HAVING
                matchCount > 0
            ORDER BY
                matchCount DESC, totalIngredients ASC;";

                        $stmt = $this->entityManager->getConnection()->prepare($sql);
                        $results = $stmt->executeQuery()->fetchAllAssociative();

                        $recettes = [];
                        foreach ($results as $result) {
                            $recettes[] = [
                                'id' => $result['id'],
                                'nom' => $result['nom'] ?? 'Nom inconnu',
                                'instructions' => $result['instructions'] ?? '',
                                'tempsPreparation' => $result['temps_preparation'] ?? '10',
                                'tempsCuisson' => $result['temps_cuisson'] ?? '10',
                                'difficulte' => $result['difficulte'] ?? 'facile',
                                'date_creation' => $result['date_creation'] ,
                                'nombre_de_portion' => $result['nombre_de_portions'] ?? '10',
                                'matchCount' => $result['matchCount'] ?? 0,
                                'totalIngredients' => $result['totalIngredients'] ?? 0,
                                'matchedIngredientNames' => isset($result['matchedIngredientNames']) ? explode(',', $result['matchedIngredientNames']) : [],
                            ];
                        }

                        return ['recettes' => $recettes];

                    } catch (\Exception $e) {
                        echo "Une erreur est survenue : " . $e->getMessage();
                        return [];
                    }
                }


                /**
                 * Extrait et normalise les noms d'ingrédients depuis différents types d'input
                 */
                private function extractIngredientNames(array $ingredients): array
                {
                    $normalizedNames = [];

                    foreach ($ingredients as $ingredient) {
                        $name = null;

                        if (is_string($ingredient)) {
                            $name = $ingredient;
                        } elseif (is_object($ingredient)) {
                            // Support pour les entités Ingredient
                            if (method_exists($ingredient, 'getNom')) {
                                $name = $ingredient->getNom();
                            } elseif (method_exists($ingredient, 'getName')) {
                                $name = $ingredient->getName();
                            } elseif (isset($ingredient->nom)) {
                                $name = $ingredient->nom;
                            }
                        } elseif (is_array($ingredient) && isset($ingredient['nom'])) {
                            $name = $ingredient['nom'];
                        }

                        if ($name !== null) {
                            $normalized = $this->normalizeIngredientName($name);
                            if (!empty($normalized)) {
                                $normalizedNames[] = $normalized;
                            }
                        }
                    }

                    // Supprimer les doublons
                    return array_unique($normalizedNames);
                }

                /**
                 * Normalise un nom d'ingrédient
                 */
                private function normalizeIngredientName(string $name): string
                {
                    // Nettoyer et normaliser
                    $normalized = trim($name);
                    $normalized = strtolower($normalized);

                    // Supprimer les caractères spéciaux si nécessaire
                    $normalized = preg_replace('/[^\p{L}\p{N}\s\-\']/u', '', $normalized);

                    // Supprimer les espaces multiples
                    $normalized = preg_replace('/\s+/', ' ', $normalized);

                    return trim($normalized);
                }

                /**
                 * Version de fallback avec une approche plus simple
                 */
                private function searchByIngredientsSimple(array $ingredients): array
                {
                    $ingredientNames = $this->extractIngredientNames($ingredients);

                    if (empty($ingredientNames)) {
                        return [];
                    }

                    // Récupérer toutes les recettes qui ont au moins un ingrédient correspondant
                    $qb = $this->entityManager->createQueryBuilder();
                    $qb->select('DISTINCT r')
                        ->from(Recette::class, 'r')
                        ->join('r.ingredients', 'i')
                        ->where('LOWER(TRIM(i.nom)) IN (:ingredientNames)')
                        ->setParameter('ingredientNames', $ingredientNames);

                    $recettes = $qb->getQuery()->getResult();

                    // Traitement en PHP pour calculer les scores
                    $scoredRecipes = [];

                    foreach ($recettes as $recette) {
                        // Récupérer tous les ingrédients de cette recette
                        $allIngredients = $recette->getIngredients();
                        $totalIngredients = count($allIngredients);

                        // Compter les correspondances
                        $matchCount = 0;
                        $matchedIngredientNames = [];

                        foreach ($allIngredients as $ingredient) {
                            $normalizedName = $this->normalizeIngredientName($ingredient->getNom());
                            if (in_array($normalizedName, $ingredientNames)) {
                                $matchCount++;
                                $matchedIngredientNames[] = $ingredient->getNom();
                            }
                        }

                        if ($matchCount > 0) {
                            $scores = $this->calculateScores($matchCount, $totalIngredients, count($ingredientNames));

                            $scoredRecipes[] = [
                                'recette' => $recette,
                                'score' => $scores['main'],
                                'detailedScores' => $scores,
                                'matchCount' => $matchCount,
                                'totalIngredients' => $totalIngredients,
                                'missingIngredients' => $totalIngredients - $matchCount,
                                'matchedIngredients' => $matchedIngredientNames,
                                'coverage' => count($ingredientNames) > 0 ? ($matchCount / count($ingredientNames)) * 100 : 0,
                                'searchedIngredients' => $ingredientNames
                            ];
                        }
                    }

                    return $this->sortResults($scoredRecipes);
                }

                /**
                 * Traite les résultats de la requête et calcule les scores
                 */
                private function processSearchResults(array $results, array $searchedIngredients): array
                {
                    $scoredRecipes = [];
                    $searchedCount = count($searchedIngredients);

                    foreach ($results as $result) {
                        $recipe = $result[0];
                        $matchCount = (int)$result['matchCount'];
                        $totalIngredients = (int)$result['totalIngredients'];
                        $matchedIngredientNames = !empty($result['matchedIngredientNames']) ?
                            explode(',', $result['matchedIngredientNames']) : [];

                        // Calculer différents types de scores
                        $scores = $this->calculateScores($matchCount, $totalIngredients, $searchedCount);

                        $scoredRecipes[] = [
                            'recette' => $recipe,
                            'score' => $scores['main'], // Score principal pour l'affichage
                            'detailedScores' => $scores,
                            'matchCount' => $matchCount,
                            'totalIngredients' => $totalIngredients,
                            'missingIngredients' => $totalIngredients - $matchCount,
                            'matchedIngredients' => $matchedIngredientNames,
                            'coverage' => $searchedCount > 0 ? ($matchCount / $searchedCount) * 100 : 0,
                            'searchedIngredients' => $searchedIngredients
                        ];
                    }

                    // Trier les résultats
                    return $this->sortResults($scoredRecipes);
                }

                /**
                 * Calcule différents types de scores pour une recette
                 */
                private function calculateScores(int $matchCount, int $totalIngredients, int $searchedCount): array
                {
                    $scores = [
                        'main' => 0,
                        'coverage' => 0,      // % des ingrédients recherchés trouvés
                        'precision' => 0,     // % des ingrédients de la recette qui correspondent
                        'efficiency' => 0     // Score hybride privilégiant les recettes avec moins d'ingrédients totaux
                    ];

                    if ($totalIngredients > 0 && $searchedCount > 0) {
                        // Score de couverture : combien des ingrédients recherchés sont présents
                        $scores['coverage'] = ($matchCount / $searchedCount) * 100;

                        // Score de précision : quelle proportion de la recette correspond
                        $scores['precision'] = ($matchCount / $totalIngredients) * 100;

                        // Score d'efficacité : favorise les recettes simples avec beaucoup de correspondances
                        $efficiency = $scores['precision'];
                        if ($totalIngredients <= 10) {
                            $efficiency *= 1.2; // Bonus pour les recettes simples
                        }
                        $scores['efficiency'] = min($efficiency, 100);

                        // Score principal : moyenne pondérée privilégiant la couverture
                        $scores['main'] = ($scores['coverage'] * 0.6) + ($scores['precision'] * 0.4);
                    }

                    return $scores;
                }

                /**
                 * Trie les résultats selon plusieurs critères
                 */
                private function sortResults(array $scoredRecipes): array
                {
                    usort($scoredRecipes, function ($a, $b) {
                        // 1. Trier par score principal (descendant)
                        $scoreComparison = $b['score'] <=> $a['score'];
                        if ($scoreComparison !== 0) {
                            return $scoreComparison;
                        }

                        // 2. En cas d'égalité, privilégier plus de correspondances
                        $matchComparison = $b['matchCount'] <=> $a['matchCount'];
                        if ($matchComparison !== 0) {
                            return $matchComparison;
                        }

                        // 3. En cas d'égalité, privilégier moins d'ingrédients manquants
                        return $a['missingIngredients'] <=> $b['missingIngredients'];
                    });

                    return $scoredRecipes;
                }

                /**
                 * Version avec requête native SQL pour les cas complexes
                 */
                public function searchByIngredientsSQL(array $ingredients): array
                {
                    $ingredientNames = $this->extractIngredientNames($ingredients);

                    if (empty($ingredientNames)) {
                        return [];
                    }

                    $placeholders = str_repeat('?,', count($ingredientNames) - 1) . '?';

                    $sql = "
        SELECT
            r.id,
            r.nom as recette_nom,
            COUNT(DISTINCT matched_i.id) as match_count,
            COUNT(DISTINCT all_i.id) as total_ingredients,
            GROUP_CONCAT(DISTINCT matched_i.nom) as matched_ingredient_names
        FROM recette r
        LEFT JOIN recette_ingredient ri ON r.id = ri.recette_id
        LEFT JOIN ingredient all_i ON ri.ingredient_id = all_i.id
        LEFT JOIN ingredient matched_i ON ri.ingredient_id = matched_i.id
            AND LOWER(TRIM(matched_i.nom)) IN ($placeholders)
        GROUP BY r.id, r.nom
        HAVING match_count > 0
        ORDER BY match_count DESC, total_ingredients ASC
    ";

                    try {
                        $stmt = $this->entityManager->getConnection()->prepare($sql);
                        $stmt->executeQuery($ingredientNames);
                        $results = $stmt->fetchAllAssociative();

                        // Convertir les résultats SQL en objets Recette
                        $scoredRecipes = [];
                        foreach ($results as $row) {
                            $recette = $this->entityManager->getRepository(Recette::class)->find($row['id']);
                            if ($recette) {
                                $matchCount = (int)$row['match_count'];
                                $totalIngredients = (int)$row['total_ingredients'];
                                $scores = $this->calculateScores($matchCount, $totalIngredients, count($ingredientNames));

                                $scoredRecipes[] = [
                                    'recette' => $recette,
                                    'score' => $scores['main'],
                                    'detailedScores' => $scores,
                                    'matchCount' => $matchCount,
                                    'totalIngredients' => $totalIngredients,
                                    'missingIngredients' => $totalIngredients - $matchCount,
                                    'matchedIngredients' => $row['matched_ingredient_names'] ?
                                        explode(',', $row['matched_ingredient_names']) : [],
                                    'coverage' => count($ingredientNames) > 0 ? ($matchCount / count($ingredientNames)) * 100 : 0,
                                    'searchedIngredients' => $ingredientNames
                                ];
                            }
                        }

                        return $this->sortResults($scoredRecipes);

                    } catch (\Exception $e) {
                        error_log('Erreur SQL recherche: ' . $e->getMessage());
                        return $this->searchByIngredientsSimple($ingredients);
                    }
                }
            }

/*
use App\Entity\Recette;
use Doctrine\ORM\EntityManagerInterface;

class RecetteSearchService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function searchByIngredients(array $ingredientNoms): array
    {
        if (empty($ingredientNoms)) {
            return [];
        }

        // Normaliser les noms d'ingrédients (gérer les objets Ingredient et les strings)
        $normalizedNames = [];
        foreach ($ingredientNoms as $ingredient) {
            if (is_string($ingredient)) {
                $normalizedNames[] = strtolower(trim($ingredient));
            } elseif (is_object($ingredient) && method_exists($ingredient, 'getNom')) {
                $normalizedNames[] = strtolower(trim($ingredient->getNom()));
            }
        }

        $qb = $this->entityManager->createQueryBuilder();

        // Requête pour trouver les recettes avec score de correspondance
        $qb->select('r', 'COUNT(ri.id) as matchCount', 'COUNT(allRi.id) as totalIngredients')
            ->from(Recette::class, 'r')
            ->leftJoin('r.ingredients', 'ri')
            ->leftJoin('ri.ingredients', 'i')
            ->leftJoin('r.ingredients', 'allRi')
            ->where('LOWER(i.nom) IN (:ingredientNoms)')
            ->setParameter('ingredientNoms', $normalizedNames) // Correction: setParameter au lieu de setParomter
            ->groupBy('r.id')
            ->having('matchCount > 0')
            ->orderBy('matchCount', 'DESC')
            ->addOrderBy('totalIngredients', 'ASC');

        $results = $qb->getQuery()->getResult();

        // Calculer le score de correspondance pour chaque recette
        $scoredRecettes = []; // Correction: nom de variable cohérent
        foreach ($results as $result) {
            $recette = $result[0];
            $matchCount = $result['matchCount'];
            $totalIngredients = $result['totalIngredients'];

            // Score = pourcentage d'ingrédients correspondants
            $score = ($matchCount / $totalIngredients) * 100;

            $scoredRecettes[] = [
                'recette' => $recette,
                'score' => $score,
                'matchCount' => $matchCount,
                'totalIngredients' => $totalIngredients,
                'missingIngredients' => $totalIngredients - $matchCount
            ];
        }

        // Trier par score puis par nombre d'ingrédients manquants
        usort($scoredRecettes, function($a, $b) {
            if ($a['score'] === $b['score']) {
                return $a['missingIngredients'] <=> $b['missingIngredients'];
            }
            return $b['score'] <=> $a['score'];
        });

        return $scoredRecettes;
    }

    public function getRecettesByExactMatch(array $ingredientNoms): array // Correction: nom de méthode cohérent
    {
        if (empty($ingredientNoms)) {
            return [];
        }

        $normalizedNames = array_map(function($nom) {
            return strtolower(trim($nom));
        }, $ingredientNoms);

        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('r')
            ->from(Recette::class, 'r')
            ->leftJoin('r.ingredients', 'ri')
            ->leftJoin('ri.ingredients', 'i')
            ->where('LOWER(i.nom) IN (:ingredientNoms)')
            ->setParameter('ingredientNoms', $normalizedNames) // Correction: setParameter au lieu de setParomter
            ->groupBy('r.id')
            ->having('COUNT(ri.id) = :ingredientCount')
            ->setParameter('ingredientCount', count($normalizedNames)); // Correction: setParameter et $normalizedNames au lieu de $normalizedNoms

        return $qb->getQuery()->getResult();
    }
}
/*
 *
    #[Route('/search', nom: 'app_recette_search')]
    public function search(Request $request): Response
    {
        //$this->entityManager

        // Récupérer tous les ingrédients pour l'affichage
        $ingredients = $this->entityManager->getRepository(Ingredient::class)
            ->findBy([], ['nom' => 'ASC']);

        $selectedIngredients = [];
        $recettes = [];
        $searchPerformed = false;

        // Si c'est une recherche POST
        if ($request->isMethod('POST')) {
            $selectedIngredientIds = $request->request->all('ingredients') ?? [];

            if (!empty($selectedIngredientIds)) {
                // Récupérer les ingrédients sélectionnés
                $selectedIngredients = $this->entityManager->getRepository(Ingredient::class)
                    ->findBy(['id' => $selectedIngredientIds]);

                // Rechercher les recettes
                $recettes = $this->searchRecetteByIngredients($selectedIngredients);
                $searchPerformed = true;
            }
        }

        return $this->render('recette/search.html.twig', [
            'ingredients' => $ingredients,
            'selectedIngredients' => $selectedIngredients,
            'recettes' => $recettes,
            'searchPerformed' => $searchPerformed,
        ]);
    }

    /**
     * Recherche de recettes par ingrédients avec scoring
     */
/*private function searchRecetteByIngredients(array $selectedIngredients): array
{
    if (empty($selectedIngredients)) {
        return [];
    }

    // Extraire les noms des ingrédients
    $ingredientNoms = array_map(fn($ingredient) => $ingredient->getNom(), $selectedIngredients);

    // Requête pour trouver les recettes contenant ces ingrédients
    $qb = $this->entityManager->createQueryBuilder();

    $qb->select('r', 'COUNT(DISTINCT ri.id) as matchCount', 'COUNT(DISTINCT allRi.id) as totalIngredients')
        ->from(Recette::class, 'r')
        ->leftJoin('r.recetteIngredients', 'ri', 'WITH', 'ri.ingredient IN (
               SELECT ing FROM App\Entity\Ingredient ing WHERE ing.nom IN (:ingredientNoms)
           )')
        ->leftJoin('r.recetteIngredients', 'allRi')
        ->setParomter('ingredientNoms', $ingredientNoms)
        ->groupBy('r.id')
        ->having('matchCount > 0')
        ->orderBy('matchCount', 'DESC')
        ->addOrderBy('totalIngredients', 'ASC');

    $results = $qb->getQuery()->getResult();

    // Traiter les résultats pour ajouter le score
    $scoredRecettes = [];
    foreach ($results as $result) {
        $recette = $result[0];
        $matchCount = $result['matchCount'];
        $totalIngredients = $result['totalIngredients'];

        // Calculer le pourcentage de correspondance
        $score = $totalIngredients > 0 ? ($matchCount / $totalIngredients) * 100 : 0;

        $scoredRecettes[] = [
            'recette' => $recette,
            'score' => round($score),
            'matchCount' => $matchCount,
            'totalIngredients' => $totalIngredients,
            'missingIngredients' => $totalIngredients - $matchCount
        ];
    }

    // Trier par score décroissant, puis par nombre d'ingrédients manquants
    usort($scoredRecettes, function($a, $b) {
        if ($a['score'] === $b['score']) {
            return $a['missingIngredients'] <=> $b['missingIngredients'];
        }
        return $b['score'] <=> $a['score'];
    });

    return $scoredRecettes;
}

}
*/
/*#[Route('/search', nom: 'app_recette_search')]
    public function search(Request $request): Response
{
    //$this->entityManager

    // Récupérer tous les ingrédients pour l'affichage
    $ingredients = $this->entityManager->getRepository(Ingredient::class)
        ->findBy([], ['nom' => 'ASC']);

    $selectedIngredients = [];
    $recettes = [];
    $searchPerformed = false;

    // Si c'est une recherche POST
    if ($request->isMethod('POST')) {
        $selectedIngredientIds = $request->request->all('ingredients') ?? [];

        if (!empty($selectedIngredientIds)) {
            // Récupérer les ingrédients sélectionnés
            $selectedIngredients = $this->entityManager->getRepository(Ingredient::class)
                ->findBy(['id' => $selectedIngredientIds]);

            // Rechercher les recettes
            $recettes = $this->searchRecetteByIngredients($selectedIngredients);
            $searchPerformed = true;
        }
    }

    return $this->render('recette/search.html.twig', [
        'ingredients' => $ingredients,
        'selectedIngredients' => $selectedIngredients,
        'recettes' => $recettes,
        'searchPerformed' => $searchPerformed,
    ]);
}

*/
