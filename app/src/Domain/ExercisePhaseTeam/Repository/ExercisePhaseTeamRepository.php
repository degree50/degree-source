<?php

namespace App\Domain\ExercisePhaseTeam\Repository;

use App\Domain\ExercisePhase\Model\ExercisePhase;
use App\Domain\ExercisePhaseTeam\Model\ExercisePhaseTeam;
use App\Domain\Solution\Model\Solution;
use App\Domain\User\Model\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExercisePhaseTeam|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExercisePhaseTeam|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExercisePhaseTeam[]    findAll()
 * @method ExercisePhaseTeam[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExercisePhaseTeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExercisePhaseTeam::class);
    }

    /**
     * @return ExercisePhaseTeam[]
     */
    public function findAllByPhaseExcludingTests(ExercisePhase $exercisePhase): iterable
    {
        return $this->createQueryBuilder('team')
            ->andWhere('team.exercisePhase = :exercisePhase')
            ->andWhere('team.isTest = false')
            ->setParameter('exercisePhase', $exercisePhase)
            ->orderBy('team.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param ExercisePhase $exercisePhase
     * @param ExercisePhaseTeam $currentTeam
     *
     * @return ExercisePhaseTeam[]
     */
    public function findOtherTeamsByPhaseExcludingTests(ExercisePhase $exercisePhase, ExercisePhaseTeam $currentTeam): iterable
    {
        return $this->createQueryBuilder('team')
            ->andWhere('team.exercisePhase = :exercisePhase')
            ->andWhere('team.isTest = false')
            ->andWhere('team != :currentTeam')
            ->setParameter('exercisePhase', $exercisePhase)
            ->setParameter('currentTeam', $currentTeam)
            ->orderBy('team.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByMemberAndExercisePhase(User $member, ExercisePhase $exercisePhase, bool $testMode = false): ?ExercisePhaseTeam
    {
        return $this->createQueryBuilder('team')
            ->andWhere(':member MEMBER OF team.members')
            ->andWhere('team.exercisePhase = :exercisePhase')
            ->andWhere('team.isTest = :testMode')
            ->setParameter('member', $member)
            ->setParameter('exercisePhase', $exercisePhase)
            ->setParameter('testMode', $testMode)
            ->orderBy('team.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findBySolution(Solution $solution): ?ExercisePhaseTeam
    {
        return $this->createQueryBuilder('team')
            ->andWhere('team.solution = :solution')
            ->setParameter('solution', $solution)
            ->orderBy('team.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
