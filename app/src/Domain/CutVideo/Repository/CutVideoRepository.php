<?php

namespace App\Domain\CutVideo\Repository;

use App\Domain\CutVideo\Model\CutVideo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CutVideoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CutVideo::class);
    }

    public function deleteCutVideo(CutVideo $cutVideo): void
    {
        $this->getEntityManager()->remove($cutVideo);
        $this->getEntityManager()->flush();
    }

    public function add(CutVideo $cutVideo): void
    {
        $this->getEntityManager()->persist($cutVideo);
        $this->getEntityManager()->flush();
    }
}
