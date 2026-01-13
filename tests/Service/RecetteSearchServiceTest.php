<?php
namespace App\Tests\Service;

use App\Services\RecetteSearchService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RecetteSearchServiceTest extends WebTestCase
{
    private RecetteSearchService $recetteSearchService;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();

        $this->entityManager = $container->get('doctrine')->getManager();
        $this->recetteSearchService = new RecetteSearchService($this->entityManager);
    }

    public function testSearchRecipeByIngredients(): void
    {
        // Arrange
        $ingredients = ['tomate', 'oignon', 'ail'];

        // Act
        $result = $this->recetteSearchService->searchByIngredients($ingredients);

        // Assert
        $this->assertIsArray($result);
    }

    public function testSearchReturnEmptyArrayForNoIngredients(): void
    {
        $result = $this->recetteSearchService->searchByIngredients([]);
        $this->assertEmpty($result);
    }

    public function testSearchFiltersByDifficulty(): void
    {
        $ingredients = ['tomate'];
        $result = $this->recetteSearchService->searchByIngredients($ingredients, 'facile');
        $this->assertIsArray($result);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if ($this->entityManager->isOpen()) {
            $this->entityManager->clear();
            $this->entityManager->getConnection()->close();
        }
    }
}
