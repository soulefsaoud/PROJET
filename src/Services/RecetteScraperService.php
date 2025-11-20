<?php

namespace App\Services;

use App\Entity\Recette;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class RecetteScraperService
{
    private Client $httpClient;
    private EntityManagerInterface $entityManager;
    private RecetteParserService $parser;

    public function __construct(
        EntityManagerInterface $entityManager,
        RecetteParserService $parser
    ) {
        $this->httpClient = new Client([
            'timeout' => 30,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (compatible; RecipeBot/1.0)'
            ]
        ]);
        $this->entityManager = $entityManager;
        $this->parser = $parser;
    }

    public function scrapeRecipe(string $url): ?Recette
    {
        try {
            $response = $this->httpClient->get($url);
            $html = $response->getBody()->getContents();

            $crawler = new Crawler($html);

            // Vérifier d'abord les données structurées JSON-LD
            $recipe = $this->parser->parseJsonLd($crawler, $url);

            if (!$recipe) {
                // Fallback sur extraction manuelle
                $recipe = $this->parser->parseManual($crawler, $url);
            }

            if ($recipe) {
                $this->entityManager->persist($recipe);
                $this->entityManager->flush();
            }

            return $recipe;

        } catch (\Exception $e) {
            // Log l'erreur
            error_log("Erreur scraping $url: " . $e->getMessage());
            return null;
        }
    }

    public function scrapeMultipleRecipes(array $urls): array
    {
        $results = [];

        foreach ($urls as $url) {
            $recipe = $this->scrapeRecipe($url);
            $results[] = [
                'url' => $url,
                'success' => $recipe !== null,
                'recipe' => $recipe
            ];

            // Pause entre les requêtes pour être respectueux
            sleep(2);
        }

        return $results;
    }
}
