<?php

namespace App\Domain\CourseRole\Repository;

use App\Domain\CourseRole\Model\CourseRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CourseRole|null find($id, $lockMode = null, $lockVersion = null)
 * @method CourseRole|null findOneBy(array $criteria, array $orderBy = null)
 * @method CourseRole[]    findAll()
 * @method CourseRole[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CourseRoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CourseRole::class);
    }
}
