<?php
namespace App\Repository;

use App\Entity\IngredientRecette;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 *  @extends ServiceEntityRepository<IngredientRecette>
 * /*/
class IngredientRecetteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IngredientRecette::class);
    }

    /**
     * Trouver les ingrédients d'une recette spécifique.
     *
     * @param int $recetteId L'ID de la recette
     * @return IngredientRecette[] Retourne un tableau d'objets IngredientRecette
     */
    public function findByRecetteId(int $recetteId): array
    {
        return $this->createQueryBuilder('ir')
            ->andWhere('ir.recette = :recetteId')
            ->setParameter('recetteId', $recetteId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouver les recettes contenant un ingrédient spécifique.
     *
     * @param int $ingredientId L'ID de l'ingrédient
     * @return IngredientRecette[] Retourne un tableau d'objets IngredientRecette
     */
    public function findByIngredientId(int $ingredientId): array
    {
        return $this->createQueryBuilder('ir')
            ->andWhere('ir.ingredient = :ingredientId')
            ->setParameter('ingredientId', $ingredientId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouver les ingrédients avec une quantité spécifique dans une recette.
     *
     * @param int $recetteId L'ID de la recette
     * @param int $quantite La quantité de l'ingrédient
     * @return IngredientRecette[] Retourne un tableau d'objets IngredientRecette
     */
    public function findByRecetteIdAndQuantite(int $recetteId, int $quantite): array
    {
        return $this->createQueryBuilder('ir')
            ->andWhere('ir.recette = :recetteId')
            ->andWhere('ir.quantite = :quantite')
            ->setParameter('recetteId', $recetteId)
            ->setParameter('quantite', $quantite)
            ->getQuery()
            ->getResult();

    }
}

