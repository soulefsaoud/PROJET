<?php
namespace App\Tests\Controller;

use App\Entity\Recette;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RecetteControllerTest extends WebTestCase
{
    public function testRecipeListingPageIsSuccessful(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
    }

    public function testRecipeSearchPageIsSuccessful(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/search-recipes');
        $this->assertResponseIsSuccessful();
    }

    public function testRecipeDetailPageIsSuccessful(): void
    {
        $client = static::createClient();
        $client->request('GET', '/recette/1');
        $this->assertThat(
            $client->getResponse()->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(200),
                $this->equalTo(404)
            )
        );
    }

    public function testIndex(): void
    {
        $client = static::createClient();

        // Récupérer l'EntityManager via le conteneur de services
        $entityManager = $client->getContainer()->get('doctrine')->getManager();

        // Créer une recette fictive
        $recette = new Recette();
        $recette->setNom('Recette de test');
        $recette->setDescriptions('Description de test');
        $recette->setInstructions('Instructions de test');
        $recette->setTempsPreparation(10);
        $recette->setTempsCuisson(20);
        $recette->setDifficulte('Facile');
        $recette->setNombreDePortions(4);
        $recette->setDateCreation(new \DateTime());
        $recette->setImageUrl('http://example.com/image.jpg');
        $recette->setImagePath('/path/to/image.jpg');
        $recette->setImageAlt('Image de test');

        // Persister la recette
        $entityManager->persist($recette);
        $entityManager->flush();

        // Faire une requête GET sur la route '/'
        $client->request('GET', '/');

        // Vérifier que la réponse est réussie
        $this->assertResponseIsSuccessful();

        // Vérifier que la page contient le nom de la recette
        $this->assertSelectorTextContains('html', 'Recette de test');

        // Nettoyer : supprimer la recette après le test
        $entityManager->remove($recette);
        $entityManager->flush();
    }
}
