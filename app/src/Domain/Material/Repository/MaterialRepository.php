<?php

namespace App\Domain\Material\Repository;

use App\Domain\Material\Model\Material;
use App\Domain\User\Model\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Material|null find($id, $lockMode = null, $lockVersion = null)
 * @method Material|null findOneBy(array $criteria, array $orderBy = null)
 * @method Material[]    findAll()
 * @method Material[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MaterialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Material::class);
    }

    public function findByOwner(User $user)
    {
        return $this->createQueryBuilder('material')
            ->andWhere('material.owner = :user')
            ->setParameter('user', $user)
            ->orderBy('material.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function removeAllMaterialsOfUser(User $user): void
    {
        $this->createQueryBuilder('material')
            ->delete()
            ->where('material.owner = :owner')
            ->setParameter('owner', $user)
            ->getQuery()
            ->execute();
    }
}
