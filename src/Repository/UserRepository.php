<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @return User[]
     */
    public function findUsersAfterCursor(int $cursor, int $limit): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.id > :cursor')
            ->setParameter('cursor', $cursor)
            ->orderBy('u.id')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
