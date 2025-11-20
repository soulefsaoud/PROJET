<?php

namespace App\Repository;

use App\Entity\Recette;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recette>
 */
class RecetteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recette::class);
    }

    //    /**
    //     * @return Recette[] Returns an array of Recette objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Recette
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    // src/Repository/RecipeRepository.php
    // src/Repository/RecipeRepository.php
    public function findByIngredientsQuery(array $ingredients, ?string $regime = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('r')
            ->join('r.ingredients', 'i')
            ->where('i.nom IN (:ingredients)')
            ->setParameter('ingredients', $ingredients)
            ->groupBy('r.id')
            ->orderBy('COUNT(i.id)', 'DESC'); // Trier par nombre d'ingrédients correspondants

        if ($regime) {
            $qb->andWhere('r.regime = :regime')
                ->setParameter('regime', $regime);
        }

        return $qb;
    }
}
