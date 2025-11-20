<?php
// src/Repository/UserIngredientRepository.php

namespace App\Repository;

use App\Entity\UserIngredient;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserIngredientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserIngredient::class);
    }

    /**
     * Trouve tous les ingrédients d'un utilisateur
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('ui')
            ->innerJoin('ui.ingredient', 'i')
            ->where('ui.user = :user')
            ->setParameter('user', $user)
            ->orderBy('ui.addedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les ingrédients qui expirent bientôt
     */
    public function findExpiringIngredients(User $user, int $days = 7): array
    {
        $date = new \DateTime();
        $date->add(new \DateInterval('P' . $days . 'D'));

        return $this->createQueryBuilder('ui')
            ->innerJoin('ui.ingredient', 'i')
            ->where('ui.user = :user')
            ->andWhere('ui.dateExpiration IS NOT NULL')
            ->andWhere('ui.dateExpiration <= :date')
            ->setParameter('user', $user)
            ->setParameter('date', $date)
            ->orderBy('ui.dateExpiration', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les ingrédients disponibles pour un utilisateur (avec leurs IDs)
     */
    public function findAvailableIngredientIds(User $user): array
    {
        $result = $this->createQueryBuilder('ui')
            ->select('IDENTITY(ui.ingredient) as ingredient_id')
            ->where('ui.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getArrayResult();

        return array_column($result, 'ingredient_id');
    }

    /**
     * Vérifie si un utilisateur a un ingrédient spécifique
     */
    public function hasIngredient(User $user, int $ingredientId): bool
    {
        $count = $this->createQueryBuilder('ui')
            ->select('COUNT(ui.id)')
            ->where('ui.user = :user')
            ->andWhere('ui.ingredient = :ingredient')
            ->setParameter('user', $user)
            ->setParameter('ingredient', $ingredientId)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }
}
