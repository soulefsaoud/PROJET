<?php
namespace App\Controller;

use App\Services\RecipeAIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AIRecipeController extends AbstractController
{
    #[Route('/ai-recipes', name: 'ai_recipes', methods: ['GET', 'POST'])]
    public function index(Request $request, RecipeAIService $aiService): Response
    {
        $recipes = [];
        $ingredients = [];

        if ($request->isMethod('POST')) {
            $ingredientsInput = $request->request->get('ingredients');
            $ingredients = array_filter(array_map('trim', explode(',', $ingredientsInput)));

            if (!empty($ingredients)) {
                $recipes = $aiService->generateRecipes($ingredients);
            }
        }

        return $this->render('ai_recipe/index.html.twig', [
            'recipes' => $recipes,
            'ingredients' => implode(', ', $ingredients)
        ]);
    }

    #[Route('/api/ai-recipes', name: 'api_ai_recipes', methods: ['POST'])]
    public function apiGenerate(Request $request, RecipeAIService $aiService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $ingredients = $data['ingredients'] ?? [];

        if (empty($ingredients)) {
            return new JsonResponse(['error' => 'Ingrédients requis'], 400);
        }

        $recipes = $aiService->generateRecipes($ingredients);
        return new JsonResponse($recipes);
    }

    #[Route('/test-ai-simple', name: 'test_ai_simple')]
    public function testSimple(RecipeAIService $aiService): Response
    {
        // Test avec des ingrédients simples
        $testIngredients = ['tomate', 'basilic', 'mozzarella'];

        try {
            $recipes = $aiService->generateRecipes($testIngredients, 2);

            return new Response(
                '<h1>Test API IA</h1>' .
                '<p><strong>Ingrédients testés :</strong> ' . implode(', ', $testIngredients) . '</p>' .
                '<pre>' . print_r($recipes, true) . '</pre>'
            );
        } catch (\Exception $e) {
            return new Response(
                '<h1>Erreur Test</h1>' .
                '<p style="color: red;">' . $e->getMessage() . '</p>'
            );
        }
    }

    #[Route('/debug-ai-direct', name: 'debug_ai_direct')]
    public function debugAIDirect(): Response
    {
        $apiKey = $_ENV['GEMINI_API_KEY'];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $apiKey,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode([
                'contents' => [
                'parts' => [['text' => 'Donne-moi 1 recette avec tomate et basilic']]
            ]
        ])
    ]);

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    return new Response(
        '<h1>Test Direct API</h1>' .
        '<p><strong>Code HTTP :</strong> ' . $httpCode . '</p>' .
        '<p><strong>Réponse :</strong></p>' .
        '<pre>' . $response . '</pre>'
    );
}

    #[Route('/test-ingredients', name: 'test_ingredients')]
    public function testMultipleIngredients(RecipeAIService $aiService): Response
    {
        $testCases = [
            ['pomme', 'cannelle'],
            ['chocolat', 'banane', 'avoine'],
            ['poulet', 'curry', 'riz'],
            ['fromage', 'jambon'],
            ['courgette', 'parmesan']
        ];

        $results = [];

        foreach ($testCases as $ingredients) {
            try {
                $recipes = $aiService->generateRecipes($ingredients, 1);
                $results[] = [
                    'ingredients' => $ingredients,
                    'success' => true,
                    'recipes' => $recipes
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'ingredients' => $ingredients,
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $this->render('test/ingredients.html.twig', [
            'results' => $results
        ]);
    }


    #[Route('/test-cache', name: 'test_cache')]
    public function testCache(RecipeAIService $aiService): Response
    {
        $ingredients = ['tomate', 'basilic'];

        // 1er appel (API call)
        $start1 = microtime(true);
        $recipes1 = $aiService->generateRecipes($ingredients);
        $time1 = (microtime(true) - $start1) * 1000;

        // 2ème appel (cache hit)
        $start2 = microtime(true);
        $recipes2 = $aiService->generateRecipes($ingredients);
        $time2 = (microtime(true) - $start2) * 1000;

        return new Response(
            "<h1>Test Cache</h1>" .
            "<p><strong>1er appel (API):</strong> {$time1}ms</p>" .
            "<p><strong>2ème appel (Cache):</strong> {$time2}ms</p>" .
            "<p><strong>Accélération:</strong> " . round($time1/$time2) . "x plus rapide !</p>"
        );
    }

    #[Route('/test-ia-complet', name: 'test_ia_complet')]
    public function testIAComplet(RecipeAIService $aiService): Response
    {
        $testCases = [
            // Tests basiques
            [['tomate', 'basilic'], 'Combinaison classique'],
            [['pomme', 'cannelle'], 'Dessert simple'],
            // Tests complexes
            [['poulet', 'curry', 'riz', 'lait de coco'], 'Plat complet'],
            [['chocolat', 'banane', 'avoine', 'miel'], 'Petit-déjeuner sain'],
            // Tests limites
            [['sel'], 'Un seul ingrédient'],
            [['kiwi', 'anchois', 'chocolat'], 'Combinaison bizarre'],
            // Tests avec accents/caractères spéciaux
            [['crème', 'gruyère', 'œufs'], 'Caractères spéciaux'],
        ];

        $results = [];
        foreach ($testCases as [$ingredients, $description]) {
            try {
                $recipe = $aiService->generateRecipe($ingredients);
                $results[] = [
                    'ingredients' => $ingredients,
                    'description' => $description,
                    'recipe' => $recipe,
                    'status' => 'success',
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'ingredients' => $ingredients,
                    'description' => $description,
                    'error' => $e->getMessage(),
                    'status' => 'error',
                ];
            }
        }



        $results = [];
        $totalTime = 0;

        foreach ($testCases as $ingredients => $description) {
            $ingredientsArray = is_array($ingredients) ? $ingredients : [$ingredients];

            $startTime = microtime(true);

            try {
                $recipes = $aiService->generateRecipes($ingredientsArray, 2);
                $executionTime = (microtime(true) - $startTime) * 1000;
                $totalTime += $executionTime;

                $results[] = [
                    'ingredients' => $ingredientsArray,
                    'description' => $description,
                    'success' => !isset($recipes['error']),
                    'recipes' => $recipes,
                    'time_ms' => round($executionTime, 2),
                    'recipe_count' => is_array($recipes) && !isset($recipes['error']) ? count($recipes) : 0
                ];

            } catch (\Exception $e) {
                $results[] = [
                    'ingredients' => $ingredientsArray,
                    'description' => $description,
                    'success' => false,
                    'error' => $e->getMessage(),
                    'time_ms' => round((microtime(true) - $startTime) * 1000, 2)
                ];
            }

            // Petit délai pour éviter le rate limiting
            usleep(200000); // 0.2 seconde
        }

        return $this->render('test/ia_complet.html.twig', [
            'results' => $results,
            'total_time' => round($totalTime, 2),
            'average_time' => round($totalTime / count($results), 2),
            'success_rate' => round((count(array_filter($results, fn($r) => $r['success'])) / count($results)) * 100, 1)
        ]);
    }

    #[Route('/test-erreurs-api', name: 'test_erreurs_api')]
    public function testErreursAPI(HttpClientInterface $httpClient): Response
    {
        $tests = [
            'Clé API invalide' => 'FAKE_KEY_123',
            'Clé API vide' => '',
            'Quota dépassé' => $_ENV['GEMINI_API_KEY'] // On teste avec vraie clé mais beaucoup de requêtes
        ];

        $results = [];

        foreach ($tests as $testName => $apiKey) {
            try {
                $response = $httpClient->request('POST',
                    'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $apiKey,
                    [
                        'json' => [
                            'contents' => [
                            'parts' => [['text' => 'Test erreur']]
                        ]
                    ]
                ]
            );

            $results[$testName] = [
                'success' => true,
                'status' => $response->getStatusCode(),
                'response' => 'OK'
            ];

        } catch (\Exception $e) {
                $results[$testName] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'handled' => true // Votre app gère bien l'erreur
                ];
            }
        }

        return new Response('<pre>' . print_r($results, true) . '</pre>');
    }

    #[Route('/test-performance', name: 'test_performance')]
    public function testPerformance(RecipeAIService $aiService): Response
    {
        $ingredients = ['tomate', 'basilic', 'mozzarella'];

        // Test cache froid (1er appel)
        $start1 = microtime(true);
        $recipes1 = $aiService->generateRecipes($ingredients);
        $coldTime = (microtime(true) - $start1) * 1000;

        // Test cache chaud (2ème appel)
        $start2 = microtime(true);
        $recipes2 = $aiService->generateRecipes($ingredients);
        $warmTime = (microtime(true) - $start2) * 1000;

        // Test charge (10 appels simultanés)
        $startLoad = microtime(true);
        for ($i = 0; $i < 10; $i++) {
            $aiService->generateRecipes(['test' . $i]);
        }
        $loadTime = (microtime(true) - $startLoad) * 1000;

        return new Response("
        <h1>🚀 Tests de Performance</h1>
        <p><strong>Cache froid (1er appel) :</strong> {$coldTime}ms</p>
        <p><strong>Cache chaud (2ème appel) :</strong> {$warmTime}ms</p>
        <p><strong>Accélération cache :</strong> " . round($coldTime/$warmTime) . "x plus rapide</p>
        <p><strong>10 appels simultanés :</strong> {$loadTime}ms (" . round($loadTime/10) . "ms/appel)</p>
        <p><strong>Verdict :</strong> " . ($warmTime < 100 ? "✅ Excellent" : ($warmTime < 500 ? "⚠️ Correct" : "❌ Lent")) . "</p>
    ");
    }

    #[Route('/test-pwa', name: 'test_pwa')]
    public function testPWA(): Response
    {
        return $this->render('test/pwa.html.twig');
    }


    #[Route('/test-offline', name: 'test_offline')]
    public function testOffline(): Response
    {
        return $this->render('test/offline.html.twig');
    }

}
