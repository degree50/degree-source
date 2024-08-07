<?php

namespace App\Domain\ExercisePhase\Repository;

use App\Domain\Exercise\Model\Exercise;
use App\Domain\ExercisePhase\Model\ExercisePhase;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExercisePhase|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExercisePhase|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExercisePhase[]    findAll()
 * @method ExercisePhase[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExercisePhaseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExercisePhase::class);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findFirstExercisePhase(Exercise $exercise): ?ExercisePhase
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        return $this->createQueryBuilder('e')
            ->where('e.belongsToExercise = :exercise')
            ->andWhere(
                $qb->expr()->eq('e.sorting', 0)
            )
            ->setMaxResults(1)
            ->setParameter('exercise', $exercise)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findExercisePhaseAfter(ExercisePhase $exercisePhase): ?ExercisePhase
    {
        return $this->findExercisePhasesLargerThen($exercisePhase->getSorting(), $exercisePhase->getBelongsToExercise());
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findExercisePhasesLargerThen(int $sorting, Exercise $exercise): ?ExercisePhase
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        return $this->createQueryBuilder('e')
            ->where('e.belongsToExercise = :exercise')
            ->andWhere(
                $qb->expr()->gt('e.sorting', $sorting)
            )
            ->orderBy('e.sorting', 'ASC')
            ->setMaxResults(1)
            ->setParameter('exercise', $exercise)
            ->getQuery()
            ->getOneOrNullResult();

    }

    /**
     * @throws NonUniqueResultException
     */
    public function findExercisePhaseBefore(ExercisePhase $exercisePhase): ?ExercisePhase
    {
        return $this->findExercisePhasesLesserThen($exercisePhase->getSorting(), $exercisePhase->getBelongsToExercise());
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findExercisePhasesLesserThen(int $sorting, Exercise $exercise): ?ExercisePhase
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        return $this->createQueryBuilder('e')
            ->where('e.belongsToExercise = :exercise')
            ->andWhere(
                $qb->expr()->lt('e.sorting', $sorting)
            )
            ->orderBy('e.sorting', 'DESC')
            ->setMaxResults(1)
            ->setParameter('exercise', $exercise)
            ->getQuery()
            ->getOneOrNullResult();

    }

    public function findAllSortedBySorting($exercise)
    {
        return $this->createQueryBuilder('e')
            ->where('e.belongsToExercise = :exercise')
            ->orderBy('e.sorting', 'ASC')
            ->setParameter('exercise', $exercise)
            ->getQuery()
            ->getResult();

    }
}
