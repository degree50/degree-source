<?php

namespace App\Domain\VideoFavorite\Repository;

use App\Domain\User\Model\User;
use App\Domain\Video\Model\Video;
use App\Domain\VideoFavorite\Model\VideoFavorite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VideoFavorite|null find($id, $lockMode = null, $lockVersion = null)
 * @method VideoFavorite|null findOneBy(array $criteria, array $orderBy = null)
 * @method VideoFavorite[]    findAll()
 * @method VideoFavorite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VideoFavoritesRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
    )
    {
        parent::__construct($registry, VideoFavorite::class);
    }

    public function findByUser(User $user)
    {
        return $this->createQueryBuilder('videoFavorite')
            ->andWhere('videoFavorite.user = :user')
            ->setParameter('user', $user)
            ->orderBy('videoFavorite.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByUserAndVideo(User $user, Video $video)
    {
        return $this->createQueryBuilder('videoFavorite')
            ->andWhere('videoFavorite.user = :user')
            ->setParameter('user', $user)
            ->andWhere('videoFavorite.video = :video')
            ->setParameter('video', $video)
            ->orderBy('videoFavorite.id', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function removeAllWithVideo(Video $video): void
    {
        $this->createQueryBuilder('videoFavorite')
            ->delete()
            ->where('videoFavorite.video = :video')
            ->setParameter('video', $video)
            ->getQuery()
            ->execute();
    }
}
