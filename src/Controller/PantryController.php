<?php
// src/Controller/PantryController.php

namespace App\Controller;

use App\Entity\UserIngredient;
use App\Entity\Ingredient;
use App\Form\UserIngredientType;
use App\Repository\UserIngredientRepository;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/pantry')]
#[IsGranted('ROLE_USER')]
class PantryController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserIngredientRepository $userIngredientRepository,
        private IngredientRepository $ingredientRepository
    ) {}

    /**
     * Afficher le garde-manger de l'utilisateur
     */
    #[Route('/', name: 'pantry_index')]
    public function index(): Response
    {
        $user = $this->getUser();

        // Récupérer les ingrédients du garde-manger
        $userIngredients = $this->userIngredientRepository->findByUser($user);

        return $this->render('pantry/index.html.twig', [
            'userIngredients' => $userIngredients,
        ]);
    }

    /**
     * Ajouter un ingrédient au garde-manger
     */
    #[Route('/add', name: 'pantry_add')]
    public function add(Request $request): Response
    {
        $user = $this->getUser();
        $userIngredient = new UserIngredient();
        $userIngredient->setUser($user);
        $userIngredient->setAddedAt(new \DateTime());

        $form = $this->createForm(UserIngredientType::class, $userIngredient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si l'ingrédient existe déjà
            $existingIngredient = $this->userIngredientRepository->findOneBy([
                'user' => $user,
                'ingredient' => $userIngredient->getIngredient()
            ]);

            if ($existingIngredient) {
                // Mettre à jour la quantité existante
                $existingIngredient->setQuantity(
                    $existingIngredient->getQuantity() + $userIngredient->getQuantity()
                );
                $existingIngredient->setExpiryDate($userIngredient->getExpiryDate());
                $existingIngredient->setAddedAt(new \DateTimeImmutable());

                $this->addFlash('success', 'Quantité mise à jour avec succès !');
            } else {
                // Ajouter le nouvel ingrédient
                $this->entityManager->persist($userIngredient);
                $this->addFlash('success', 'Ingrédient ajouté au garde-manger !');
            }

            $this->entityManager->flush();
            return $this->redirectToRoute('pantry_index');
        }

        return $this->render('pantry/add.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * Modifier un ingrédient du garde-manger
     */
    #[Route('/edit/{id}', name: 'pantry_edit')]
    public function edit(UserIngredient $userIngredient, Request $request): Response
    {
        // Vérifier que l'ingrédient appartient à l'utilisateur connecté
        if ($userIngredient->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(UserIngredientType::class, $userIngredient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'Ingrédient modifié avec succès !');
            return $this->redirectToRoute('pantry_index');
        }

        return $this->render('pantry/edit.html.twig', [
            'form' => $form,
            'userIngredient' => $userIngredient,
        ]);
    }

    /**
     * Supprimer un ingrédient du garde-manger
     */
    #[Route('/delete/{id}', name: 'pantry_delete')]
    public function delete(UserIngredient $userIngredient, Request $request): Response
    {
        // Vérifier que l'ingrédient appartient à l'utilisateur connecté
        if ($userIngredient->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // Vérification CSRF
        if ($this->isCsrfTokenValid('delete' . $userIngredient->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($userIngredient);
            $this->entityManager->flush();
            $this->addFlash('success', 'Ingrédient supprimé du garde-manger !');
        }

        return $this->redirectToRoute('pantry_index');
    }

    /**
     * Recherche d'ingrédients pour l'autocomplétion
     */
    #[Route('/search-ingredients', name: 'pantry_search_ingredients')]
    public function searchIngredients(Request $request): Response
    {
        $query = $request->query->get('q', '');

        if (strlen($query) < 2) {
            return $this->json([]);
        }

        $ingredients = $this->ingredientRepository->findByNameLike($query);

        $results = [];
        foreach ($ingredients as $ingredient) {
            $results[] = [
                'id' => $ingredient->getId(),
                'nom' => $ingredient->getNom(),
            ];
        }

        return $this->json($results);
    }

    /**
     * Afficher les ingrédients qui expirent bientôt
     */
    #[Route('/expiring', name: 'pantry_expiring')]
    public function expiring(): Response
    {
        $user = $this->getUser();
        $expiringIngredients = $this->userIngredientRepository->findExpiringIngredients($user, 7); // 7 jours

        return $this->render('pantry/expiring.html.twig', [
            'expiringIngredients' => $expiringIngredients,
        ]);
    }
}
