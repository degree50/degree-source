<?php

namespace App\Domain\Video\Repository;

use App\Domain\Course\Model\Course;
use App\Domain\User\Model\User;
use App\Domain\Video\Model\Video;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class VideoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Video::class);
    }

    /**
     * @return Video[]
     */
    public function findAllForUser(User $user): array
    {
        $qb = $this
            ->createQueryBuilder('video')
            ->orderBy('video.createdAt', 'DESC');


        if (!$user->isAdmin()) {
            $videosInUserCourses = $user->getCourses()->reduce(
                fn($videos, Course $course) => [...$videos, ...$course->getVideos()->toArray()],
                []
            );

            $qb
                // current user is the creator of the video
                ->orWhere('video.creator = :user')
                // current user is in a course that the video is assigned to
                ->orWhere('video.id IN (:videosInUserCourses)')
                ->setParameter('user', $user)
                ->setParameter('videosInUserCourses', $videosInUserCourses);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    public function findAllForCourse(Course $course): array
    {
        $qb = $this
            ->createQueryBuilder('video')
            ->orderBy('video.createdAt', 'DESC');


        $qb
            ->andWhere(':course MEMBER OF video.courses')
            ->setParameter('course', $course);

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Course $course
     * @return Video[]|iterable
     */
    public function findByCourse(Course $course): iterable
    {
        return $this->createQueryBuilder('v')
            ->andWhere(':course MEMBER OF v.courses')
            ->setParameter('course', $course)
            ->orderBy('v.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByCreator(User $user): array
    {
        return $this->findBy(['creator' => $user]);
    }

    public function deleteVideo(Video $video): void
    {
        $this->getEntityManager()->remove($video);
        $this->getEntityManager()->flush();
    }
}
