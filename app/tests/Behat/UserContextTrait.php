<?php

namespace App\Tests\Behat;

use App\Domain\Course\Model\Course;
use App\Domain\CourseRole\Model\CourseRole;
use App\Domain\ExercisePhaseTeam\Model\ExercisePhaseTeam;
use App\Domain\User\Model\User;
use App\Domain\Attachment\Model\Attachment;
use App\Domain\Exercise\Model\Exercise;
use App\Domain\Video\Model\Video;
use App\Security\Voter\DataPrivacyVoter;
use App\Security\Voter\TermsOfUseVoter;
use DateTimeImmutable;
use InvalidArgumentException;
use function PHPUnit\Framework\assertContains;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNotEquals;
use function PHPUnit\Framework\assertTrue;

trait UserContextTrait
{
    private function createUser(
        string $username,
        string $password = null,
        bool $acceptPrivacyAndTerms = false,
        bool $isStudent = false,
        bool $isVerified = true
    ): User {
        $user = new User($username);
        $user->setEmail($username);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $password ?? 'password'));

        if ($isStudent) {
            $user->setIsStudent(true);
        } else {
            $user->setIsDozent(true);
        }

        $user->setIsVerified($isVerified);

        // TODO: Put in separate step
        if ($acceptPrivacyAndTerms) {
            // accept current Privacy & Terms
            $user->setDataPrivacyAccepted(true);
            $user->setDataPrivacyVersion(DataPrivacyVoter::DATA_PRIVACY_VERSION);
            $user->setTermsOfUseAccepted(true);
            $user->setTermsOfUseVersion(TermsOfUseVoter::TERMS_OF_USE_VERSION);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * @Given A user :username exists
     */
    public function aUserExists(string $username): void
    {
        $user = $this->getUserByEmail($username);
        if (!$user) {
            $this->createUser($username, null, true);
        }
    }


    /**
     * @Given A User :username with the role :role exists
     */
    public function aUserWithTheRoleExists($username, $role): void
    {
        // create user if it does not exist
        $this->aUserExists($username);

        // add the role
        $user = $this->getUserByEmail($username);
        $user->setRoles([$role]);

        // persist
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * @When I delete User :username
     */
    public function iDeleteUser($username): void
    {
        $user = $this->getUserByEmail($username);
        $this->userService->removeUser($user);
    }

    /**
     * @Given The User :username has CourseRole :courseRole in Course :courseId
     */
    public function userHasCourseRole($username, $courseRoleRole, $courseId): void
    {
        if (!in_array($courseRoleRole, CourseRole::ROLES)) {
            throw new InvalidArgumentException(
                'Invalid CourseRole! Expected one of [' .
                    implode(', ', CourseRole::ROLES) .
                    ']. Given: "' . $courseRoleRole . '".'
            );
        }

        $course = $this->entityManager->find(Course::class, $courseId);
        assert($course instanceof Course, 'Course not found!');

        $user = $this->getUserByEmail($username);
        assert($user instanceof User, 'User not found!');

        $courseRole = new CourseRole();
        $courseRole->setCourse($course);
        $courseRole->setUser($user);
        $courseRole->setName($courseRoleRole);

        $user->addCourseRole($courseRole);
        $course->addCourseRole($courseRole);

        $this->entityManager->persist($courseRole);
        $this->entityManager->persist($user);

        $this->entityManager->flush();
    }

    /**
     * @Then No User with Username :username does exist
     */
    public function assertUserDoesNotExist(string $username): void
    {
        $user = $this->getUserByEmail($username);
        assertEquals(null, $user);
    }

    /**
     * @Then User with Username :username should exist
     */
    public function assertUserExists(string $username): void
    {
        $user = $this->getUserByEmail($username);
        assertNotEquals(null, $user);
    }

    /**
     * @Then No CourseRole of User :username exists
     */
    public function assertNoCourseRoleOfUserExists($username): void
    {
        /** @var CourseRole[] $allCourseRoles */
        $allCourseRoles = $this->entityManager->getRepository(CourseRole::class)->findAll();
        $courseRolesOfUser = array_filter($allCourseRoles, function (CourseRole $courseRole) use ($username) {
            return $courseRole->getUser()->getUsername() === $username;
        });

        assertEquals(0, count($courseRolesOfUser));
    }

    /**
     * @Then The User :username is anonymized and their unused content removed
     */
    public function assertUserIsAnonymizedAndUnusedContentIsRemoved($username): void
    {
        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->find($username);

        /**
         * Why
         *   We check the User's username to verify that the user is correctly anonymized
         */
        assertNotEquals($username, $user->getUsername(), "Username should be anonymized.");

        // attachment
        $attachmentByUser = $this->entityManager->getRepository(Attachment::class)->findBy(['creator' => $user]);
        $attachmentNotCorrectlyRemoved = array_filter($attachmentByUser, function (Attachment $attachment) use ($username) {
            // attachment is not used in unpublished Exercise
            $notUnpublished = $attachment->getExercisePhase()->getBelongsToExercise()->getStatus() !== Exercise::EXERCISE_CREATED;
            // attachment user is not $username
            $usernameIsAnonymized = $attachment->getCreator()->getUsername() !== $username;

            return !($notUnpublished || $usernameIsAnonymized);
        });

        assertEquals(0, count($attachmentNotCorrectlyRemoved), "Unused Attachment should be removed.");

        // videos
        $videos = $this->videoRepository->findByCreator($user);
        $videosNotCorrectlyRemoved = array_filter($videos, function (Video $video) use ($username) {
            // video is not _only_ used in unpublished Exercise
            // If it is used in just a single published Exercise it has to persist
            $notAllExercisesUnpublished = !$video->getExercisePhases()
                ->forAll(fn ($_i, Exercise $exercise) => $exercise->getStatus() === Exercise::EXERCISE_CREATED);

            $creatorAnonymized = $video->getCreator()->getUsername() !== $username;

            return !($notAllExercisesUnpublished || $creatorAnonymized);
        });

        assertEquals(0, count($videosNotCorrectlyRemoved), "Unused Videos should be removed.");

        // courseRoles
        $courseRolesWithUser = $this->entityManager->getRepository(CourseRole::class)->findBy(['user' => $user]);
        assertEquals(0, count($courseRolesWithUser), "User should not have CourseRoles.");
        assertEquals(0, $user->getCourseRoles()->count(), "User should not have CourseRoles.");

        // exercises (unpublished)
        $exercises = $this->exerciseRepository->findBy(['creator' => $user]);
        $exercisesNotUnpublishedAndAnonymized =
            count(
                array_filter($exercises, function (Exercise $exercise) use ($username) {
                    $published = $exercise->getStatus() !== Exercise::EXERCISE_CREATED;
                    $usernameAnonymized = $exercise->getCreator()->getUsername() !== $username;

                    return !($published || $usernameAnonymized);
                })
            ) === 0;

        assertTrue($exercisesNotUnpublishedAndAnonymized, "No unpublished Exercises should remain.");

        // teams
        $teams = $this->entityManager->getRepository(ExercisePhaseTeam::class)->findAll();
        $teamsWithUser = array_filter($teams, function (ExercisePhaseTeam $team) use ($user, $username) {
            $userIsCreator = $team->getCreator() === $user;
            $userIsMember = $team->getMembers()->contains($user);

            return $userIsCreator || $userIsMember;
        });

        assertEquals(0, count($teamsWithUser), "User should not be in any ExercisePhaseTeam.");
    }

    /**
     * @Given the expiration date of user :username is set to :relativeTime from now
     */
    public function theExpirationDateOfUserIsSetTo(string $username, string $relativeTime): void
    {
        $user = $this->getUserByEmail($username);
        $user->setExpirationDate(new DateTimeImmutable($relativeTime));

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * @Then the expiration date of user :username should be set to :relativeTime from now
     */
    public function theExpirationDateOfUserShouldBeSetTo(string $username, string $relativeTime): void
    {
        $user = $this->getUserByEmail($username);

        $expectedDateDiff = (new DateTimeImmutable($relativeTime))->diff($user->getExpirationDate());
        assertEquals($expectedDateDiff->days, 0);
    }

    /**
     * @When expired users are removed
     */
    public function expiredUsersAreRemoved(): void
    {
        $this->userExpirationService->removeAllExpiredUsers();
    }

    /**
     * @When expiring users are notified
     */
    public function expiringUsersAreNotified(): void
    {
        $this->userExpirationService->notifySoonToBeExpiredUsers();
    }

    /**
     * @Then User :username should be marked as notified
     */
    public function userShouldBeMarkedAsNotified(string $username): void
    {
        $user = $this->getUserByEmail($username);
        assertTrue($user->isExpirationNoticeSent());
    }

    /**
     * @Then User :username should be marked as not notified
     */
    public function userShouldBeMarkedAsNotNotified(string $username): void
    {
        $user = $this->getUserByEmail($username);
        assertFalse($user->isExpirationNoticeSent());
    }

    /**
     * @Then User :username should only have the role :role
     */
    public function userShouldHaveTheRole(string $username, string $role): void
    {
        $user = $this->getUserByEmail($username);
        assertContains($role, $user->getRoles());
    }

    /**
     * @Given User :username is marked as not verified
     */
    public function userIsMarkedAsNotVerified(string $username): void
    {
        $user = $this->getUserByEmail($username);
        $user->setIsVerified(false);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * @Given User :username is marked as verified
     */
    public function userIsMarkedAsVerified(string $username): void
    {
        $user = $this->getUserByEmail($username);
        $user->setIsVerified(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    private function getUserByEmail(string $email): User|null
    {
        /** @var User|null */
        return $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);
    }

    /**
     * @Then The User with Id :userId should be anonymized
     */
    public function theUserShouldBeAnonymized(string $userId): void
    {
        $user = $this->entityManager->getRepository(User::class)->find($userId);
        assertNotEquals($userId, $user->getUsername());
    }

    /**
     * @Then The User with Id :userId does not exist
     */
    public function theUserWithIdDoesNotExist(string $userId): void
    {
        $user = $this->entityManager->getRepository(User::class)->find($userId);
        assertEquals(null, $user);
    }
}
