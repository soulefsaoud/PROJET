<?php
// src/Services/RecetteParserService.php
namespace App\Services;

use App\Entity\Recette;
use App\Entity\Ingredient;
use App\Entity\IngredientRecette;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;

class RecetteParserService
{
    private $imageDownloadService;
    private $entityManager;

    public function __construct(ImageDownloadService $imageDownloadService, EntityManagerInterface $entityManager)
    {
        $this->imageDownloadService = $imageDownloadService;
        $this->entityManager = $entityManager;
    }

    public function parseJsonLd(Crawler $crawler, string $url): ?Recette
    {
        $scriptTags = $crawler->filter('script[type="application/ld+json"]');
        foreach ($scriptTags as $script) {
            $data = json_decode($script->textContent, true);
            if ($data && isset($data['@type']) && $data['@type'] === 'Recipe') {
                return $this->createRecipeFromJsonLd($data, $url, $crawler);
            }
        }
        return null;
    }

    public function parseManual(Crawler $crawler, string $url): ?Recette
    {
        $recipe = new Recette();
        $recipe->setUrl($url);

        try {
            $recipe->setInstructions('Instructions non disponibles pour cette recette.');

            $title = $crawler->filter('h1')->first();
            $nom = 'Recette sans nom';
            if ($title->count() > 0) {
                $titleText = trim($title->text());
                if (!empty($titleText)) {
                    $nom = $titleText;
                }
            }
            $recipe->setNom($nom);

            $this->entityManager->persist($recipe);

            $crawler->filter('.recipe-ingredient, .ingredient')->each(
                function (Crawler $node) use ($recipe) {
                    $ingredientText = trim($node->text());
                    if (!empty($ingredientText)) {
                        $this->createIngredientRecette($ingredientText, $recipe);
                    }
                }
            );

            $instructions = [];
            $crawler->filter('.recipe-instruction, .instruction, .recipe_instructions, .instructions, .recipe-steps, .steps, .recipe-preparation, .preparation')->each(
                function (Crawler $node) use (&$instructions) {
                    $text = trim($node->text());
                    if (!empty($text)) {
                        $instructions[] = $text;
                    }
                }
            );

            if (!empty($instructions)) {
                $recipe->setInstructions(implode("\n", $instructions));
            }

            $this->processImage($crawler, $recipe, $url);

            $this->validateRequiredFields($recipe);

            $this->entityManager->flush();

            return $recipe;
        } catch (\Exception $e) {
            error_log("Erreur lors du parsing manuel: " . $e->getMessage());
            $this->entityManager->clear(); // Nettoie l'EntityManager en cas d'erreur
            return null;
        }
    }

    private function createRecipeFromJsonLd(array $data, string $url, Crawler $crawler): Recette
    {
        $recipe = new Recette();
        $recipe->setSourceUrl($url);
        $recipe->setInstructions('Instructions en cours d\'extraction...');

        $nom = trim($data['name'] ?? '');
        if (empty($nom)) {
            $titleElement = $crawler->filter('h1, title, .recipe-title')->first();
            $nom = $titleElement->count() > 0 ? trim($titleElement->text()) : 'Recette sans nom';
        }
        $recipe->setNom($nom);

        $this->entityManager->persist($recipe);

        if (isset($data['image'])) {
            $this->processImageFromJsonLd($data['image'], $recipe, $url);
        } else {
            $this->extractImage($crawler, $recipe, $url);
        }

        if (isset($data['recipeIngredient'])) {
            $ingredients = is_array($data['recipeIngredient']) ? $data['recipeIngredient'] : [$data['recipeIngredient']];
            foreach ($ingredients as $ingredientText) {
                $this->createIngredientRecette($ingredientText, $recipe);
            }
        }

        $instructions = [];
        if (isset($data['recipeInstructions']) && is_array($data['recipeInstructions'])) {
            foreach ($data['recipeInstructions'] as $instruction) {
                if (is_string($instruction)) {
                    $text = trim($instruction);
                    if (!empty($text)) {
                        $instructions[] = $text;
                    }
                } elseif (is_array($instruction) && isset($instruction['text'])) {
                    $text = trim($instruction['text']);
                    if (!empty($text)) {
                        $instructions[] = $text;
                    }
                }
            }
        }

        if (empty($instructions)) {
            $instructions = $this->extractInstructionsFromHtml($crawler);
        }

        $instructionsText = !empty($instructions) ? implode("\n", $instructions) : 'Instructions non disponibles pour cette recette.';
        $recipe->setInstructions($instructionsText);

        if (isset($data['prepTime'])) {
            $recipe->setTempsPreparation($this->parseIso8601Duration($data['prepTime']));
        }

        if (isset($data['cookTime'])) {
            $recipe->setTempsCuisson($this->parseIso8601Duration($data['cookTime']));
        }

        if (isset($data['recipeYield'])) {
            $yield = is_array($data['recipeYield']) ? $data['recipeYield'][0] : $data['recipeYield'];
            $recipe->setNombreDePortions((int)$yield);
        }

        $this->validateRequiredFields($recipe);

      $this->entityManager->flush();

        return $recipe;
    }

    private function processImage(Crawler $crawler, Recette $recipe, string $url): void
    {
        $this->extractImage($crawler, $recipe, $url);
    }

    private function processImageFromJsonLd($imageData, Recette $recipe, string $baseUrl): void
    {
        $imageUrl = null;
        $imageAlt = null;

        if (is_string($imageData)) {
            $imageUrl = $imageData;
        } elseif (is_array($imageData)) {
            if (isset($imageData[0])) {
                $firstImage = $imageData[0];
                if (is_string($firstImage)) {
                    $imageUrl = $firstImage;
                } elseif (isset($firstImage['url'])) {
                    $imageUrl = $firstImage['url'];
                    $imageAlt = $firstImage['caption'] ?? null;
                }
            }
        }

        if ($imageUrl) {
            $imageUrl = $this->resolveImageUrl($imageUrl, $baseUrl);
            $recipe->setImageUrl($imageUrl);
            $recipe->setImageAlt($imageAlt ?? $recipe->getNom());
            $imagePath = $this->imageDownloadService->downloadImage($imageUrl, $recipe->getNom() ?: 'recipe');
            if ($imagePath) {
                $recipe->setImagePath($imagePath);
            }
        }
    }

    private function extractImage(Crawler $crawler, Recette $recipe, string $baseUrl): void
    {
        $imageUrl = null;

        // 1. Open Graph (Facebook)
        try {
            $og = $crawler->filter('meta[property="og:image"]')->first();
            if ($og->count() > 0) {
                $imageUrl = $og->attr('content');
            }
        } catch (\Exception $e) {}

        // 2. Twitter Card
        if (!$imageUrl) {
            try {
                $twitter = $crawler->filter('meta[name="twitter:image"]')->first();
                if ($twitter->count() > 0) {
                    $imageUrl = $twitter->attr('content');
                }
            } catch (\Exception $e) {}
        }

        // 3. Première image de la page
        if (!$imageUrl) {
            try {
                $firstImg = $crawler->filter('img')->first();
                if ($firstImg->count() > 0) {
                    $imageUrl = $firstImg->attr('src') ?: $firstImg->attr('data-src');
                }
            } catch (\Exception $e) {}
        }

        if ($imageUrl && strlen($imageUrl) > 10) {
            $imageUrl = $this->resolveImageUrl($imageUrl, $baseUrl);
            $recipe->setImageUrl($imageUrl);
            $recipe->setImageAlt($recipe->getNom());
            $imagePath = $this->imageDownloadService->downloadImage($imageUrl, $recipe->getNom() ?: 'recipe');
            if ($imagePath) {
                $recipe->setImagePath($imagePath);
            }
        }
    }

    private function resolveImageUrl(string $imageUrl, string $baseUrl): string
    {
        if (strpos($imageUrl, 'http') === 0) {
            return $imageUrl;
        }

        $parsedBaseUrl = parse_url($baseUrl);
        $scheme = $parsedBaseUrl['scheme'];
        $host = $parsedBaseUrl['host'];

        if (strpos($imageUrl, '//') === 0) {
            return $scheme . ':' . $imageUrl;
        }

        if (strpos($imageUrl, '/') === 0) {
            return $scheme . '://' . $host . $imageUrl;
        }

        return $scheme . '://' . $host . '/' . $imageUrl;
    }

    private function validateRequiredFields(Recette $recipe): void
    {
        if (empty($recipe->getNom())) {
            $recipe->setNom('Recette sans nom');
        }

        $instructions = $recipe->getInstructions();
        if ($instructions === null || trim($instructions) === '') {
            $recipe->setInstructions('Instructions non disponibles pour cette recette.');
        }
    }

    private function createIngredientRecette(string $ingredientText, Recette $recipe): void
    {
        try {
            $parsedIngredient = $this->parseIngredientText($ingredientText);
            if (empty($parsedIngredient['nom'])) {
                throw new \Exception("Impossible de parser l'ingrédient: {$ingredientText}");
            }

            $ingredient = $this->getOrCreateIngredient($parsedIngredient['nom']);
            if (!$ingredient) {
                throw new \Exception("Impossible de créer l'ingrédient: {$parsedIngredient['nom']}");
            }

            $ingredientRecette = new IngredientRecette();
            $ingredientRecette->setRecette($recipe);
            $ingredientRecette->setIngredient($ingredient);
            $ingredientRecette->setQuantite($parsedIngredient['quantite']);
            $ingredientRecette->setUniteMesure($parsedIngredient['unite']);

            $this->entityManager->persist($ingredientRecette);
        } catch (\Exception $e) {
            error_log("Erreur lors du traitement de l'ingrédient '{$ingredientText}': " . $e->getMessage());
        }
    }

    private function parseIngredientText(string $ingredientText): array
    {
        $result = [
            'nom' => '',
            'quantite' => 1,
            'unite' => 'unité'
        ];

        $ingredientText = trim($ingredientText);

        if (preg_match('/^(\d+(?:[.,]\d+)?)\s*([a-zA-ZÀ-ÿ]+)?\s+(.+)/', $ingredientText, $matches)) {
            $result['quantite'] = (float)str_replace(',', '.', $matches[1]);
            $result['unite'] = $matches[2] ?: 'unité';
            $result['nom'] = trim($matches[3]);
        } else {
            $result['nom'] = $ingredientText;
        }

        return $result;
    }

    private function getOrCreateIngredient(string $nomIngredient): ?Ingredient
    {
        $nomNormalise = strtolower(trim($nomIngredient));

        $ingredientRepo = $this->entityManager->getRepository(Ingredient::class);
        $ingredient = $ingredientRepo->findOneBy(['nom' => $nomNormalise]);

        if (!$ingredient) {
            $ingredient = new Ingredient();
            $ingredient->setNom($nomNormalise);
            $ingredient->setCategorie('Autre');
            $this->entityManager->persist($ingredient);
          $this->entityManager->flush();
        }

        return $ingredient;
    }

    private function extractInstructionsFromHtml(Crawler $crawler): array
    {
        $instructions = [];
        $selectors = [
            '.recipe-instructions li',
            '.recipe-instructions p',
            '[itemprop="recipeInstructions"]',
            '.instructions li',
            '.instructions p',
            '.recipe-method li',
            '.recipe-method p',
            '.recipe-steps li',
            '.recipe-steps p',
            '.recipe-preparation li',
            '.recipe-preparation p',
            '.recipe_instructions li',
            '.recipe_instructions p',
            '.recipe_instructions .instruction',
            '.instructions .instruction',
            '.recipe-steps .step',
            '.preparation-steps li',
            '.preparation-steps p',
            '.steps li',
            '.steps p',
            '.preparation li',
            '.preparation p',
            '.method li',
            '.method p'
        ];

        foreach ($selectors as $selector) {
            $elements = $crawler->filter($selector);
            if ($elements->count() > 0) {
                $elements->each(function (Crawler $node) use (&$instructions) {
                    $text = trim($node->text());
                    if (!empty($text) && strlen($text) > 10) {
                        $instructions[] = $text;
                    }
                });

                if (count($instructions) >= 2) {
                    break;
                }
            }
        }

        return $instructions;
    }

    private function parseIso8601Duration(string $duration): int
    {
        preg_match('/PT(?:(\d+)H)?(?:(\d+)M)?/', $duration, $matches);
        $hours = isset($matches[1]) ? (int)$matches[1] : 0;
        $minutes = isset($matches[2]) ? (int)$matches[2] : 0;
        return ($hours * 60) + $minutes;
    }
}
