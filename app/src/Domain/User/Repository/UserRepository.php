<?php

namespace App\Domain\User\Repository;

use App\Domain\User\Model\User;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use function get_class;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{

    public function __construct(
        ManagerRegistry                         $registry,
        private readonly EntityManagerInterface $entityManager
    )
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * @return User[]
     */
    public function findAllExpiredUsers(): array
    {
        $now = new DateTimeImmutable();
        return $this->createQueryBuilder('user')
            ->where('user.isAnonymized != TRUE')
            ->andWhere('user.expirationDate < :now')
            ->setParameter('now', $now->format(User::DB_DATE_FORMAT))
            ->getQuery()
            ->getResult();
    }

    /**
     * @return User[]
     */
    public function findAllUsersWithVerificationTimeout(): array
    {
        // we calculate the verification deadline by subtracting the timeout duration from the current date
        // Example: createdAt < now - 5 days
        $now = new DateTimeImmutable();
        $verificationDeadline = $now
            ->sub(DateInterval::createFromDateString(User::VERIFICATION_TIMEOUT_DURATION_STRING));

        // expiration_date - notice_duration < now
        return $this->createQueryBuilder('user')
            ->where('user.isAnonymized != TRUE')
            ->andWhere('user.isVerified != TRUE')
            ->andWhere('user.createdAt < :verificationDeadline')
            ->setParameter('verificationDeadline', $verificationDeadline->format(User::DB_DATE_FORMAT))
            ->getQuery()
            ->getResult();
    }

    /**
     * @return User[]
     */
    public function findAllUnNotifiedSoonToBeExpiredUsers(): array
    {
        $now = new DateTimeImmutable();
        $notificationTimeWindowStart = $now
            ->add(DateInterval::createFromDateString(User::EXPIRATION_NOTICE_DURATION_STRING));

        // expiration_date - notice_duration < now
        return $this->createQueryBuilder('user')
            ->where('user.isAnonymized != TRUE')
            ->andWhere('user.expirationNoticeSent != TRUE')
            ->andWhere('user.expirationDate < :notificationTimeWindowStart')
            ->setParameter('notificationTimeWindowStart', $notificationTimeWindowStart->format(User::DB_DATE_FORMAT))
            ->getQuery()
            ->getResult();
    }
}
