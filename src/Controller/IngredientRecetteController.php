<?php

namespace App\Controller;


    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Routing\Annotation\Route;
    use Doctrine\Persistence\ManagerRegistry;
    use App\Entity\IngredientRecette;
    use App\Entity\Recette;
    use App\Entity\Ingredient;

class IngredientRecetteController extends AbstractController
{
    /**
     * @Route("/api/ingredientRecette", name="api_ingredient_recette", methods={"GET", "POST"})
     */
    public function getIngredientRecettes(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return new JsonResponse(['error' => 'Invalid JSON data'], 400);
            }

            $entityManager = $doctrine->getManager();

            // Exemple de création et de persistance d'une entité IngredientRecette
            $recette = new Recette();
            $recette->setNom('Crêpes');

            $ingredient = new Ingredient();
            $ingredient->setNom('Farine');

            $ingredientRecette = new IngredientRecette();
            $ingredientRecette->setRecette($recette);
            $ingredientRecette->setIngredient($ingredient);

            $entityManager->persist($recette);
            $entityManager->persist($ingredient);
            $entityManager->persist($ingredientRecette);

            $entityManager->flush();

            return new JsonResponse(['message' => 'Données reçues et persistées avec succès', 'data' => $data]);
        }

        // Traiter les requêtes GET ici
        return new JsonResponse(['message' => 'Requête GET reçue avec succès']);
    }








    /**
    * @Route("/api/ingredientRecette", name="api_ingredient_recette", methods={"GET", "POST"})
    */
//    public function getIngredientRecettes(Request $request): JsonResponse
//    {
//    if ($request->isMethod('POST')) {
//    $data = json_decode($request->getContent(), true);
//
//    if (json_last_error() !== JSON_ERROR_NONE) {
//    return new JsonResponse(['error' => 'Invalid JSON data'], 400);
//    }

    // Traiter les données POST ici
//    return new JsonResponse(['message' => 'Données reçues avec succès', 'data' => $data]);
//    }
//
    // Traiter les requêtes GET ici
//    return new JsonResponse(['message' => 'Requête GET reçue avec succès']);
//    }
}
