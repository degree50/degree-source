<?php

namespace App\Domain\Solution\Repository;

use App\Domain\Solution\Model\Solution;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Solution|null find($id, $lockMode = null, $lockVersion = null)
 * @method Solution|null findOneBy(array $criteria, array $orderBy = null)
 * @method Solution[]    findAll()
 * @method Solution[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SolutionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Solution::class);
    }
}
