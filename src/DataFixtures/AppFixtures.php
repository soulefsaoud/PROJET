<?php

namespace App\DataFixtures;

require_once 'vendor/autoload.php'; // Assurez-vous que l'autoloader de Composer est inclus

use App\Entity\Ingredient;
use App\Entity\IngredientRecette;
use App\Entity\Recette;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;


class AppFixtures extends Fixture
{



    public function load(ObjectManager $manager): void
    {
        // Créer les ingrédients
        $ingredients = [
            // Légumes
            ['nom' => 'tomate', 'categorie' => 'légume'],
            ['nom' => 'oignon', 'categorie' => 'légume'],
            ['nom' => 'ail', 'categorie' => 'légume'],
            ['nom' => 'carotte', 'categorie' => 'légume'],
            ['nom' => 'courgette', 'categorie' => 'légume'],
            ['nom' => 'poivron', 'categorie' => 'légume'],
            ['nom' => 'champignon', 'categorie' => 'légume'],
            ['nom' => 'épinard', 'categorie' => 'légume'],

            // Protéines
            ['nom' => 'poulet', 'categorie' => 'viande'],
            ['nom' => 'boeuf', 'categorie' => 'viande'],
            ['nom' => 'porc', 'categorie' => 'viande'],
            ['nom' => 'saumon', 'categorie' => 'poisson'],
            ['nom' => 'thon', 'categorie' => 'poisson'],
            ['nom' => 'oeuf', 'categorie' => 'protéine'],

            // Féculents
            ['nom' => 'riz', 'categorie' => 'féculent'],
            ['nom' => 'pâtes', 'categorie' => 'féculent'],
            ['nom' => 'pomme de terre', 'categorie' => 'féculent'],
            ['nom' => 'quinoa', 'categorie' => 'féculent'],

            // Produits laitiers
            ['nom' => 'lait', 'categorie' => 'laitier'],
            ['nom' => 'crème fraîche', 'categorie' => 'laitier'],
            ['nom' => 'fromage', 'categorie' => 'laitier'],
            ['nom' => 'beurre', 'categorie' => 'laitier'],

            // Épices et condiments
            ['nom' => 'huile d\'olive', 'categorie' => 'condiment'],
            ['nom' => 'sel', 'categorie' => 'épice'],
            ['nom' => 'poivre', 'categorie' => 'épice'],
            ['nom' => 'basilic', 'categorie' => 'herbe'],
            ['nom' => 'persil', 'categorie' => 'herbe'],
            ['nom' => 'thym', 'categorie' => 'herbe'],
        ];

        $ingredientEntities = [];
        foreach ($ingredients as $ingredientData) {
            $ingredient = new Ingredient();
            $ingredient->setnom($ingredientData['nom']);
            $ingredient->setcategorie($ingredientData['categorie']);
            $manager->persist($ingredient);
            $ingredientEntities[$ingredientData['nom']] = $ingredient;
        }

        // Créer les recettes
        $recettes = [
            [
                'nom' => 'Ratatouille Provençale',
                'instructions' => "1. Couper tous les légumes en dés.\n2. Faire revenir l'oignon et l'ail dans l'huile d'olive.\n3. Ajouter les autres légumes et laisser mijoter 30 minutes.\n4. Assaisonner avec les herbes, sel et poivre.",
                'tempsPreparation' => 20,
                'tempsCuisson' => 30,
                'nombreDePortions' => 4,
                'date_creation' => new \DateTime('2017-03-12'),
                'ingredients' => [
                    ['nom' => 'tomate', 'quantite' => '4', 'unite_mesure' => 'pièces'],
                    ['nom' => 'courgette', 'quantite' => '2', 'unite_mesure' => 'pièces'],
                    ['nom' => 'poivron', 'quantite' => '1', 'unite_mesure' => 'pièce'],
                    ['nom' => 'oignon', 'quantite' => '1', 'unite_mesure' => 'pièce'],
                    ['nom' => 'ail', 'quantite' => '3', 'unite_mesure' => 'gousses'],
                    ['nom' => 'huile d\'olive', 'quantite' => '3', 'unite_mesure' => 'cuillères à soupe'],
                    ['nom' => 'basilic', 'quantite' => '1', 'unite_mesure' => 'bouquet'],
                    ['nom' => 'thym', 'quantite' => '1', 'unite_mesure' => 'cuillère à café'],
                    ['nom' => 'sel', 'quantite' => '1', 'unite_mesure' => 'pincee'],
                    ['nom' => 'poivre', 'quantite' => '1', 'unite_mesure' => 'pincee'],
                ]
            ],
            [
                'nom' => 'Saumon Grillé aux Légumes',
                'instructions' => "1. Préchauffer le four à 200°C.\n2. Couper les légumes en morceaux.\n3. Assaisonner le saumon avec sel, poivre et huile d'olive.\n4. Cuire le saumon et les légumes au four pendant 25 minutes.",
                'tempsPreparation' => 15,
                'tempsCuisson' => 25,
                'nombreDePortions' => 2,
                'date_creation' => new \DateTime('2017-03-12'),
                'ingredients' => [
                    ['nom' => 'saumon', 'quantite' => '2', 'unite_mesure' => 'filets'],
                    ['nom' => 'courgette', 'quantite' => '1', 'unite_mesure' => 'pièce'],
                    ['nom' => 'carotte', 'quantite' => '2', 'unite_mesure' => 'pièces'],
                    ['nom' => 'oignon', 'quantite' => '1', 'unite_mesure' => 'pièce'],
                    ['nom' => 'huile d\'olive', 'quantite' => '2', 'unite_mesure' => 'cuillères à soupe'],
                    ['nom' => 'sel', 'quantite' => '1', 'unite_mesure' => 'pincee'],
                    ['nom' => 'poivre', 'quantite' => '1', 'unite_mesure' => 'pincee'],
                ]
            ],
            [
                'nom' => 'Risotto aux Champignons',
                'instructions' => "1. Faire revenir l'oignon dans le beurre.\n2. Ajouter le riz et remuer 2 minutes.\n3. Ajouter le bouillon chaud progressivement en remuant.\n4. Incorporer les champignons et le fromage en fin de cuisson.",
                'tempsPreparation' => 10,
                'temps_cuisson' => 30,
                'nombreDePortions' => 4,
                'date_creation' => new \DateTime('2017-03-12'),
                'ingredients' => [
                    ['nom' => 'riz', 'quantite' => '300', 'unite_mesure' => 'g'],
                    ['nom' => 'champignon', 'quantite' => '200', 'unite_mesure' => 'g'],
                    ['nom' => 'oignon', 'quantite' => '1', 'unite_mesure' => 'pièce'],
                    ['nom' => 'fromage', 'quantite' => '100', 'unite_mesure' => 'g'],
                    ['nom' => 'beurre', 'quantite' => '50', 'unite_mesure' => 'g'],
                    ['nom' => 'ail', 'quantite' => '2', 'unite_mesure' => 'gousses'],
                    ['nom' => 'sel', 'quantite' => '1', 'unite_mesure' => 'pincee'],
                    ['nom' => 'poivre', 'quantite' => '1', 'unite_mesure' => 'pincee'],
                ]
            ],
            [
                'nom' => 'Pâtes à la Carbonara',
                'instructions' => "1. Cuire les pâtes selon les instructions.\n2. Battre les œufs avec le fromage.\n3. Faire revenir l'ail dans l'huile d'olive.\n4. Mélanger les pâtes chaudes avec les œufs et servir immédiatement.",
                'tempsPreparation' => 5,
                'tempsCuisson' => 15,
                'nombreDePortions' => 2,
                'date_creation' => new \DateTime('2017-03-12'),
                'ingredients' => [
                    ['nom' => 'pâtes', 'quantite' => '200', 'unite_mesure' => 'g'],
                    ['nom' => 'oeuf', 'quantite' => '2', 'unite_mesure' => 'pièces'],
                    ['nom' => 'fromage', 'quantite' => '80', 'unite_mesure' => 'g'],
                    ['nom' => 'ail', 'quantite' => '2', 'unite_mesure' => 'gousses'],
                    ['nom' => 'huile d\'olive', 'quantite' => '2', 'unite_mesure' => 'cuillères à soupe'],
                    ['nom' => 'poivre', 'quantite' => '1', 'unite_mesure' => 'pincee'],
                ]
            ],
            [
                'nom' => 'Salade de Quinoa aux Légumes',
                'instructions' => "1. Cuire le quinoa selon les instructions.\n2. Couper tous les légumes en petits dés.\n3. Mélanger le quinoa refroidi avec les légumes.\n4. Assaisonner avec huile d'olive, sel et poivre.",
                'tempsPreparation' => 15,
                'tempsCuisson' => 15,
                'nombreDePortions' => 3,
                'date_creation' => new \DateTime('2017-03-12'),
                'ingredients' => [
                    ['nom' => 'quinoa', 'quantite' => '150', 'unite_mesure' => 'g'],
                    ['nom' => 'tomate', 'quantite' => '2', 'unite_mesure' => 'pièces'],
                    ['nom' => 'courgette', 'quantite' => '1', 'unite_mesure' => 'pièce'],
                    ['nom' => 'poivron', 'quantite' => '1', 'unite_mesure' => 'pièce'],
                    ['nom' => 'oignon', 'quantite' => '1', 'unite_mesure' => 'pièce'],
                    ['nom' => 'huile d\'olive', 'quantite' => '3', 'unite_mesure' => 'cuillères à soupe'],
                    ['nom' => 'persil', 'quantite' => '1', 'unite_mesure' => 'bouquet'],
                    ['nom' => 'sel', 'quantite' => '1', 'unite_mesure' => 'pincee'],
                    ['nom' => 'poivre', 'quantite' => '1', 'unite_mesure' => 'pincee'],
                ]
            ],
            [
                'nom' => 'Omelette aux Épinards',
                'instructions' => "1. Laver et égoutter les épinards.\n2. Battre les œufs avec sel et poivre.\n3. Faire fondre le beurre dans une poêle.\n4. Ajouter les épinards puis les œufs battus.\n5. Cuire en omelette et servir chaud.",
                'tempsPreparation' => 5,
                'tempsCuisson' => 10,
                'nombreDePortions' => 2,
                'date_creation' => new \DateTime('2017-03-12'),
                'ingredients' => [
                    ['nom' => 'oeuf', 'quantite' => '4', 'unite_mesure' => 'pièces'],
                    ['nom' => 'épinard', 'quantite' => '200', 'unite_mesure' => 'g'],
                    ['nom' => 'beurre', 'quantite' => '20', 'unite_mesure' => 'g'],
                    ['nom' => 'fromage', 'quantite' => '50', 'unite_mesure' => 'g'],
                    ['nom' => 'sel', 'quantite' => '1', 'unite_mesure' => 'pincee'],
                    ['nom' => 'poivre', 'quantite' => '1', 'unite_mesure' => 'pincee'],
                ]
            ]
        ];

        foreach ($recettes as $recetteData) {

            $faker = Factory::create('fr_FR'); // Crée une instance de Faker en français

            for ($i = 0; $i < 10; $i++) { // Crée 10 recettes fictives
                $recette = new Recette();
                $recette->setNom($faker->sentence(3)); // Génère un nom fictif
                $recette->setDescription($faker->paragraph);
                $recette->setInstructions($faker->text); // Génère des instructions fictives
                $recette->setTempsPreparation($faker->numberBetween(5, 60)); // Génère un temps de préparation fictif
                $recette->setTempsCuisson($faker->numberBetween(10, 120)); // Génère un temps de cuisson fictif
                $recette->setDifficulte($faker->randomElement(['Facile', 'Moyen', 'Difficile'])); // Génère une difficulté fictive
                $recette->setDateCreation($faker->dateTimeThisYear); // Génère une date de création fictive
                $recette->setNombreDePortions($faker->numberBetween(1, 10)); // Génère un nombre de portions fictif



                //$manager->persist($recette);

                foreach ($recetteData['ingredients'] as $ingredientData) {
                    if (isset($ingredientEntities[$ingredientData['nom']])) {
                        $ingredientrecette = new ingredientRecette();
                        $ingredientrecette->setRecette($recette);
                        $ingredientrecette->setIngredient($ingredientEntities[$ingredientData['nom']]);
                        $ingredientrecette->setQuantite($ingredientData['quantite']);
                        $ingredientrecette->setUniteMesure($ingredientData['unite_mesure']);

                        $recette->addingredientrecette($ingredientrecette);
                        $manager->persist($ingredientrecette);
                    }
                }
            }

                $manager->persist($recette);
            }

            $manager->flush();
        }
    }




