<?php
namespace App\Tests\Controller;

use App\Entity\Recette;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HomeControllerTest extends WebTestCase
{
    public function testHomePageIsSuccessful(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        self::assertResponseIsSuccessful();
        self::assertPageTitleContains('Compose & Enjoy');
    }

    public function testIndex(): void
    {
        // Créer un client pour simuler une requête HTTP
        $client = static::createClient();

        // Récupérer le conteneur de services via le client
        $container = $client->getContainer();

        // Récupérer l'EntityManager
        $entityManager = $container->get('doctrine')->getManager();

        // Créer une recette fictive pour le test
        $recette = new Recette();

        // Définir toutes les propriétés nécessaires de $recette ici
        $recette->setNom('Test Recette');
        $recette->setDescriptions('Description de test');
        $recette->setInstructions('Instructions de test'); // Ajout de cette ligne
        $recette->setTempsPreparation(10);
        $recette->setTempsCuisson(20);
        $recette->setDifficulte('Facile');
        $recette->setNombreDePortions(4);
        $recette->setDateCreation(new \DateTime());

        // Persister et sauvegarder la recette
        $entityManager->persist($recette);
        $entityManager->flush();

        // Faire une requête GET sur la route '/'
        $client->request('GET', '/');

        // Vérifier que la réponse est réussie (code HTTP 200)
        $this->assertResponseIsSuccessful('La réponse HTTP doit être réussie.');

        // Vérifier que la page contient le titre de la recette
        $this->assertSelectorTextContains('html', 'Test Recette');

        // Nettoyer : supprimer la recette après le test
        $entityManager->remove($recette);
        $entityManager->flush();
    }
}
