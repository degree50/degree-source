<?php

namespace App\Domain\User\Service;

use App\Domain\Attachment\Service\AttachmentService;
use App\Domain\AutosavedSolution\Model\AutosavedSolution;
use App\Domain\Course\Model\Course;
use App\Domain\CourseRole\Model\CourseRole;
use App\Domain\Exercise\Model\Exercise;
use App\Domain\Exercise\Service\ExerciseService;
use App\Domain\ExercisePhaseTeam\Model\ExercisePhaseTeam;
use App\Domain\ExercisePhaseTeam\Repository\ExercisePhaseTeamRepository;
use App\Domain\User\Model\User;
use App\Domain\Video\Service\VideoService;
use App\Domain\VideoFavorite\Service\VideoFavouritesService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * This Service handles the removal of Users from the system.
 *
 * In case of User being a Dozent we will keep their created Exercises and
 * Videos needed for those Exercises. The Dozent will be anonymized and all his rights will be removed.
 *
 * In case of User being a Student|Admin we will remove everything the User created.
 *
 * Events will not be removed as, at the time of writing, they do not seem to include any sensitive data.
 */
readonly class UserService
{
    public function __construct(
        private Security               $security,
        private EntityManagerInterface $entityManager,
        private VideoService           $videoService,
        private ExerciseService        $exerciseService,
        private VideoFavouritesService $videoFavoritesService,
        private UserMaterialService    $userMaterialService,
        private AttachmentService      $attachmentService,
    )
    {
    }

    public function getLoggendInUser(): User|null
    {
        /** @var User */
        return $this->security->getUser();
    }

    public function deleteStudent(User $user): void
    {
        if ($user->isAdmin() || $user->isDozent()) {
            throw new InvalidArgumentException('User is not a Student');
        }

        $this->exerciseService->deleteExercisesCreatedByUser($user);
        $this->videoFavoritesService->deleteFavoriteVideosByUser($user);
        $this->userMaterialService->deleteMaterialsOfUser($user);
        $this->videoService->deleteVideosCreatedByUser($user);
        $this->attachmentService->removeAttachmentsCreatedByUser($user);

        // remove user from teams
        $teamsWhereUserCanNotBeRemoved = $this->removeFromExerciseTeams($user);

        if (count($teamsWhereUserCanNotBeRemoved) === 0) {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
        } else {
            // anonymize user
            $user->setIsAnonymized(true);
            $user->setEmail(Uuid::uuid4()->toString());
            $user->setPassword(md5(random_bytes(20)));
            $user->setRoles([]);
            $user->setDataPrivacyVersion(-1);
            $user->setTermsOfUseVersion(-1);

            // set expiration date to 1 year from now
            $user->setExpirationDate(new DateTimeImmutable('+1 year'));
            // prevent emails from being sent to user due to expiration
            $user->setExpirationNoticeSent(true);

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }

    public function removeUser(User $user): void
    {
        // only Admins can remove Users
        /** @var User $currentlyLoggedInUser */
        $currentlyLoggedInUser = $this->security->getUser();
        if (!$currentlyLoggedInUser->isAdmin()) {
            throw new AccessDeniedException();
        }

        /**
         * Why
         *   The different user groups are removed differently because in some conditions we want to keep their
         *   created educational content (used exercises, used videos, used attachments).
         *   Dozent will be anonymized and the Account made practically unusable.
         */
        switch (true) {
            case $user->isAdmin():
                $this->deleteAdmin($user);
                break;
            case $user->isDozent():
                $this->deleteDozent($user);
                break;
            case $user->isStudent():
                $this->deleteStudent($user);
                break;
            default:
                throw new InvalidArgumentException('User is not a Student, Dozent or Admin');
        }
    }

    // used in twig templates
    public function userCanUploadVideo(): bool
    {
        /** @var User $user */
        $user = $this->security->getUser();
        return $this->canUploadVideo($user);
    }

    public function canUploadVideo(User $user): bool
    {
        if ($user->isAdmin() || $user->isDozent()) {
            return true;
        }

        // check if student is assigned to a course
        $userCourses = $user->getCourseRoles()->map(function (CourseRole $courseRole) {
            return $courseRole->getCourse();
        });

        return $userCourses->count() > 0;
    }

    private function deleteDozent(User $user): void
    {
        $courses = $user->getCourses();

        foreach ($courses as $course) {
            /* @var Course $course */

            $dozents = $course->getDozents();

            if (count($dozents) > 1) {
                // if user is NOT the only dozent in a course
                //     - remove user from course
                //     - transfer ownership of exercises to another dozent in the course
                $exercises = $course->getExercises();
                $nextDozent = $dozents->filter(fn($dozent) => $dozent->getId() !== $user->getId())->first();

                if ($nextDozent === null) {
                    throw new InvalidArgumentException('Could not find another Dozent in the Course');
                }

                foreach ($exercises as $exercise) {
                    /* @var Exercise $exercise */
                    $this->attachmentService->transferAttachmentsOfUserInExerciseToUser($user, $exercise, $nextDozent);

                    $exercise->setCreator($nextDozent);
                    $this->entityManager->persist($exercise);
                }


                $this->entityManager->persist($course);
            } else {
                // if user is the only dozent in a course
                //   - delete all courses where user is the only dozent
                //   - delete all attachments
                $this->attachmentService->removeAttachmentsCreatedByUser($user);
                $this->entityManager->remove($course);
            }

            $this->entityManager->flush();
        }

        $this->videoService->deleteVideosCreatedByUser($user);
        $this->videoFavoritesService->deleteFavoriteVideosByUser($user);
        $this->userMaterialService->deleteMaterialsOfUser($user);
        $teamsWhereUserIsOnlyMember = $this->removeFromExerciseTeams($user);

        foreach ($teamsWhereUserIsOnlyMember as $team) {
            $this->entityManager->remove($team);
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    private function deleteAdmin(User $user): void
    {
        // remove created Exercises, Videos and Interactions
        $this->videoService->deleteVideosCreatedByUser($user);
        $this->attachmentService->removeAttachmentsCreatedByUser($user);
        $this->exerciseService->deleteExercisesCreatedByUser($user);
        $this->videoFavoritesService->deleteFavoriteVideosByUser($user);
        $this->userMaterialService->deleteMaterialsOfUser($user);
        $teamsWhereUserIsOnlyMember = $this->removeFromExerciseTeams($user);

        foreach ($teamsWhereUserIsOnlyMember as $team) {
            $this->entityManager->remove($team);
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    /**
     * Why
     *   Remove User from ExercisePhaseTeams and remove their AutosavedSolutions.
     *   If the user is the only member then only remove AutosavedSolutions.
     *
     * @return ExercisePhaseTeam[] Teams the User could not be removed from
     */
    private function removeFromExerciseTeams(User $user): array
    {
        /* @var ExercisePhaseTeamRepository $teamRepository */
        $teamRepository = $this->entityManager->getRepository(ExercisePhaseTeam::class);
        $teamsOfUser = array_filter($teamRepository->findAll(), function (ExercisePhaseTeam $team) use ($user) {
            return $team->getMembers()->contains($user);
        });

        $teamsNotRemoved = [];

        foreach ($teamsOfUser as $team) {
            /** @var ExercisePhaseTeam $team */
            $team->getAutosavedSolutions()
                ->filter(function (AutosavedSolution $autosavedSolution) use ($user) {
                    return $autosavedSolution->getOwner() === $user;
                })
                ->forAll(fn($_i, AutosavedSolution $autosavedSolution) => $this->entityManager->remove($autosavedSolution));
            $this->entityManager->flush();

            if ($team->getMembers()->count() > 1) {
                $team->removeMember($user);

                $this->entityManager->persist($team);
                $this->entityManager->flush();
            } elseif ($team->isTest()) {
                $this->entityManager->remove($team);
                $this->entityManager->flush();
            } else {
                $teamsNotRemoved[] = $team;
            }
        }

        return $teamsNotRemoved;
    }
}
