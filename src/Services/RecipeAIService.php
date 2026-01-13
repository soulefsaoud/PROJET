<?php

namespace App\Services;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class RecipeAIService
{
    private HttpClientInterface $httpClient;
    private CacheInterface $cache;

    public function __construct(HttpClientInterface $httpClient, CacheInterface $cache)
    {
        $this->httpClient = $httpClient;
        $this->cache = $cache;
    }

    public function generateRecipes(array $ingredients, int $count = 3): array
    {
        // 1. Génération clé de cache
        sort($ingredients); // Important pour la cohérence
        $cacheKey = 'recipes_' . md5(implode('_', $ingredients) . '_' . $count);

        // 2. Utilisation du cache Symfony (élégant)
        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($ingredients, $count) {
            // Cette fonction ne s'execute QUE si pas en cache
            $item->expiresAfter(3600); // 1 heure

            // Appel API seulement si nécessaire
            return $this->callGeminiAPI($ingredients, $count);
        });
    }

    private function callGeminiAPI(array $ingredients, int $count): array
    {
        $apiKey = $_ENV['GEMINI_API_KEY'];
        $ingredientsList = implode(', ', $ingredients);

        $prompt = "Génère exactement {$count} recettes avec : {$ingredientsList}";

        try {
            $response = $this->httpClient->request('POST',
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $apiKey,
                [
                    'json' => [
                        'contents' => [
                            'parts' => [['text' => $prompt]]
                        ]
                    ]
                ]
            );

            $data = $response->toArray();
            $generatedText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

            return $this->parseRecipes($generatedText);

        } catch (\Exception $e) {
            return ['error' => 'Erreur API : ' . $e->getMessage()];
        }
    }


    private function parseRecipes(string $text): array
    {
        $recipes = [];
        $recipeBlocks = preg_split('/RECETTE \d+:/', $text);

        foreach ($recipeBlocks as $block) {
            if (trim($block)) {
                $recipe = $this->parseRecipeBlock($block);
                if ($recipe) {
                    $recipes[] = $recipe;
                }
            }
        }

        return $recipes;
    }

    private function parseRecipeBlock(string $block): ?array
    {
        $lines = explode("\n", trim($block));
        $recipe = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (strpos($line, 'Nom:') === 0) {
                $recipe['nom'] = trim(substr($line, 4));
            } elseif (strpos($line, 'Temps:') === 0) {
                $recipe['temps'] = trim(substr($line, 6));
            } elseif (strpos($line, 'Ingrédients:') === 0) {
                $recipe['ingredients'] = trim(substr($line, 12));
            } elseif (strpos($line, 'Instructions:') === 0) {
                $recipe['instructions'] = trim(substr($line, 13));
            }
        }

        return !empty($recipe['nom']) ? $recipe : null;
    }
}
