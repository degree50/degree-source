<?php

namespace App\Domain\VideoCode\Repository;

use App\Domain\VideoCode\Model\VideoCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VideoCode|null find($id, $lockMode = null, $lockVersion = null)
 * @method VideoCode|null findOneBy(array $criteria, array $orderBy = null)
 * @method VideoCode[]    findAll()
 * @method VideoCode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VideoCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VideoCode::class);
    }
}
