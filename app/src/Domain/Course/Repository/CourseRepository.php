<?php

namespace App\Domain\Course\Repository;

use App\Domain\Course\Model\Course;
use App\Domain\User\Model\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Course|null find($id, $lockMode = null, $lockVersion = null)
 * @method Course|null findOneBy(array $criteria, array $orderBy = null)
 * @method Course[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method
 */
class CourseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Course::class);
    }

    /**
     * @return Course[]
     */
    public function findAll(): array
    {
        return $this->findBy(array(), array('name' => 'ASC'));
    }

    public function findOneForUserWithCriteria(User $user, Criteria $criteria = null): ?Course
    {
        $criteria = $criteria ?? Criteria::create();

        $qb = $this
            ->createQueryBuilder('course')
            ->addCriteria($criteria);

        if (!$user->isAdmin()) {
            $userCourses = $user->getCourses();

            $qb
                // current user is in a course that the course is assigned to
                ->andWhere('course.id IN (:userCourses)')
                ->setParameter('userCourses', $userCourses);
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Course[]
     */
    public function findAllForUserWithCriteria(User $user, Criteria $criteria = null): array
    {
        $criteria = $criteria ?? Criteria::create();

        $qb = $this
            ->createQueryBuilder('course')
            ->orderBy('course.name', 'ASC')
            ->addCriteria($criteria);

        if (!$user->isAdmin()) {
            $userCourses = $user->getCourses();

            $qb
                // current user is in a course that the course is assigned to
                ->orWhere('course.id IN (:userCourses)')
                ->setParameter('userCourses', $userCourses);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}
