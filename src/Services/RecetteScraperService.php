<?php
// src/Services/RecetteScraperService.php
namespace App\Services;

use App\Entity\Recette;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DomCrawler\Crawler;

class RecetteScraperService
{
    private $httpClient;
    private $entityManager;
    private $parser;

    public function __construct(EntityManagerInterface $entityManager, RecetteParserService $parser)
    {
        $this->httpClient = new Client([
            'timeout' => 30,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (compatible; RecipeBot/1.0)',
            ],
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

            $recipe = $this->parser->parseJsonLd($crawler, $url);
            if (!$recipe) {
                $recipe = $this->parser->parseManual($crawler, $url);
            }

            return $recipe;
        } catch (GuzzleException $e) {
            error_log("Erreur HTTP lors du scraping de $url: " . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            error_log("Erreur lors du scraping de $url: " . $e->getMessage());
            return null;
        }
    }

    public function scrapeMultipleRecipes(array $urls): array
    {
        $results = [];

        foreach ($urls as $url) {
            $this->entityManager->clear(); // Nettoie l'EntityManager avant chaque nouvelle URL
            $recipe = $this->scrapeRecipe($url);

            $results[] = [
                'url' => $url,
                'success' => $recipe !== null,
                'recipe' => $recipe
            ];

            sleep(2); // Pause pour éviter de surcharger les serveurs
        }

        return $results;
    }
}
