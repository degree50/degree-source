<?php

namespace App\Domain\Attachment\Repository;

use App\Domain\Attachment\Model\Attachment;
use App\Domain\Exercise\Model\Exercise;
use App\Domain\User\Model\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Attachment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Attachment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Attachment[]    findAll()
 * @method Attachment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AttachmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Attachment::class);
    }

    public function getAttachmentsCreatedByUser(User $user): array
    {
        return $this->findBy(['creator' => $user]);
    }

    public function getAttachmentsCreatedByUserInExercise(User $user, Exercise $exercise): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.creator = :user')
            ->andWhere('a.exercisePhase IN (:exercisePhases)')
            ->setParameter('user', $user)
            ->setParameter('exercisePhases', $exercise->getPhases())
            ->getQuery()
            ->getResult();
    }
}
