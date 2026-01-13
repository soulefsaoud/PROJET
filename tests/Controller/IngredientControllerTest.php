<?php
// tests/Controller/IngredientControllerTest.php

namespace App\Tests\Controller;

use App\Entity\Ingredient;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class IngredientControllerTest extends WebTestCase
{
    private function createIngredient($entityManager): Ingredient
    {
        $ingredient = new Ingredient();
        $ingredient->setNom('Test Ingredient');
        $ingredient->setCategorie('Test Categorie');
        $ingredient->setUniteMesure('kg');

        $entityManager->persist($ingredient);
        $entityManager->flush();

        return $ingredient;
    }

    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/ingredient');
        $this->assertResponseIsSuccessful();
    }

    public function testNew(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/ingredient/new');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Save')->form();

        // Corrigé : prefix exact du formulaire généré par Symfony
        $form['ingredient1_form[nom]'] = 'Test Ingredient';
        $form['ingredient1_form[categorie]'] = 'Test Categorie';
        $form['ingredient1_form[unite_mesure]'] = 'kg';

        $client->submit($form);
        $this->assertResponseRedirects('/ingredient');
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testShow(): void
    {
        $client = static::createClient();
        $entityManager = self::getContainer()->get('doctrine')->getManager();

        $ingredient = $this->createIngredient($entityManager);

        $client->request('GET', '/ingredient/' . $ingredient->getId());
        $this->assertResponseIsSuccessful();

        // Nettoyage
        $entityManager->remove($ingredient);
        $entityManager->flush();
    }

    public function testEdit(): void
    {
        $client = static::createClient();
        $entityManager = self::getContainer()->get('doctrine')->getManager();

        $ingredient = $this->createIngredient($entityManager);

        $crawler = $client->request('GET', '/ingredient/' . $ingredient->getId() . '/edit');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Update')->form();

        $form['ingredient1_form[nom]'] = 'Updated Ingredient';
        $form['ingredient1_form[categorie]'] = 'Fruit';
        $form['ingredient1_form[unite_mesure]'] = 'g';

        $client->submit($form);
        $this->assertResponseRedirects('/ingredient');
        $client->followRedirect();
        $this->assertResponseIsSuccessful();

        // Nettoyage
        $entityManager->clear();
        $ingredient = $entityManager->find(Ingredient::class, $ingredient->getId());
        $entityManager->remove($ingredient);
        $entityManager->flush();
    }

    public function testDelete(): void
    {
        $client = static::createClient();
        $entityManager = self::getContainer()->get('doctrine')->getManager();

        $ingredient = $this->createIngredient($entityManager);
        $ingredientId = $ingredient->getId();

        // Page show pour récupérer le token CSRF
        $crawler = $client->request('GET', '/ingredient/' . $ingredientId);
        $token = $crawler->filter('input[name="_token"]')->attr('value');

        // Suppression
        $client->request('POST', '/ingredient/' . $ingredientId . '/delete', [
            '_token' => $token,
        ]);

        $this->assertResponseRedirects('/ingredient');

        // Vérifier que l’ingrédient a bien été supprimé
        $entityManager->clear();
        $deletedIngredient = $entityManager->find(Ingredient::class, $ingredientId);
        $this->assertNull($deletedIngredient, 'L’ingrédient a bien été supprimé.');
    }
}

