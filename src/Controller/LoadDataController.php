<?php

// src/Controller/LoadDataController.php

namespace App\Controller;

    use App\Entity\Recette;
    use App\Entity\Ingredient;
    use App\Entity\IngredientRecette;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Routing\Annotation\Route;


class LoadDataController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/api/load-data', name: 'load_data', methods: ['POST'])]
    public function loadData(): JsonResponse
    {
        $jsonFilePath = 'data.json';
        $jsonContent = file_get_contents($jsonFilePath);
        $data = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['error' => 'Invalid JSON data'], 400);
        }

        foreach ($data as $item) {
            if (isset($item['recette'])) {
                foreach ($item['recette'] as $recetteData) {
                    $recette = new Recette();
                    $recette->setNom($recetteData['nom']);
                    $recette->setInstructions($recetteData['instructions']);
                    $recette->setTempsPreparation($recetteData['tempsPreparation']);
                    $recette->setTempsCuisson($recetteData['tempsCuisson']);
                    $recette->setNombreDePortions($recetteData['nbDePortions']);
                    $recette->setDifficulte($recetteData['difficulte']);

                    $this->entityManager->persist($recette);
                }
            }

            if (isset($item['ingredient'])) {
                foreach ($item['ingredient'] as $ingredientData) {
                    $ingredient = new Ingredient();
                    $ingredient->setNom($ingredientData['nom']);
                    $ingredient->setUniteMesure($ingredientData['unite']);
                    $ingredient->setCategorie($ingredientData['categorie']);

                    $this->entityManager->persist($ingredient);
                }
            }

            if (isset($item['ingredientRecette'])) {
                foreach ($item['ingredientRecette'] as $ingredientRecetteData) {
                    $ingredient = $this->entityManager->getRepository(Ingredient::class)->find($ingredientRecetteData['ingredientId']);
                    $recette = $this->entityManager->getRepository(Recette::class)->find($ingredientRecetteData['recetteId']);

                    if ($ingredient && $recette) {
                        $ingredientRecette = $this->entityManager->getRepository(IngredientRecette::class)
                            ->findOneBy([
                                'ingredient' => $ingredient,
                                'recette' => $recette
                            ]);

                        if (!$ingredientRecette) {
                            $ingredientRecette = new IngredientRecette();
                            $ingredientRecette->setIngredient($ingredient);
                            $ingredientRecette->setRecette($recette);
                        }

                        $ingredientRecette->setQuantite($ingredientRecetteData['quantite']);
                        $ingredientRecette->setUniteMesure($ingredientRecetteData['unite'] ?? '');

                        $this->entityManager->persist($ingredientRecette);
                    }
                }
            }
        }

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Données chargées avec succès']);
    }
}


