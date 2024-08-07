<?php

namespace App\Schreibtisch\Service;

use App\Domain\Course\Model\Course;
use App\Domain\CourseRole\Model\CourseRole;
use App\Domain\Exercise\Model\Exercise;
use App\Domain\Exercise\Model\ExerciseStatus;
use App\Domain\Exercise\Service\ExerciseService;
use App\Domain\ExercisePhase\Model\ExercisePhase;
use App\Domain\ExercisePhase\Model\ExercisePhaseStatus;
use App\Domain\ExercisePhase\Service\ExercisePhaseService;
use App\Domain\Fachbereich\Model\Fachbereich;
use App\Domain\Material\Model\Material;
use App\Domain\User\Model\User;
use App\Domain\User\Service\UserMaterialService;
use App\Domain\User\Service\UserService;
use App\Domain\VideoFavorite\Model\VideoFavorite;
use App\Domain\VideoFavorite\Service\VideoFavouritesService;
use App\Twig\AppRuntime;
use DateTimeImmutable;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class SchreibtischService
{
    public function __construct(
        private ExerciseService        $exerciseService,
        private UserService            $userService,
        private ExercisePhaseService   $exercisePhaseService,
        private VideoFavouritesService $videoFavouritesService,
        private UserMaterialService    $materialService,
        private AppRuntime             $appRuntime,
        private UrlGeneratorInterface  $router,
    )
    {
    }

    public function getExercisesApiResponse(): array
    {
        $user = $this->userService->getLoggendInUser();
        $exercises = $this->exerciseService->getExercisesForUser($user);
        $exercisesCopy = [...$exercises];
        usort($exercisesCopy, function (Exercise $eA, Exercise $eB) use ($user) {
            $statusA = $this->exerciseService->getExerciseStatusForUser($eA, $user);
            $statusB = $this->exerciseService->getExerciseStatusForUser($eB, $user);
            $sortedByStatus = $this->sortByExerciseStatus($statusA, $statusB);

            // In this case we compared these elements and already know there current new sorting,
            // because of their status (because they are different)
            if ($sortedByStatus !== 0) {
                return $sortedByStatus;
            }

            $lastEditDateA = $this->exerciseService->getLastEditDateByUser($eA, $user);
            $lastEditDateB = $this->exerciseService->getLastEditDateByUser($eB, $user);

            return $this->sortByDateTimeImmutable($lastEditDateA, $lastEditDateB);
        });

        return array_map(function (Exercise $exercise) use ($user) {
            $fachbereich = $exercise->getCourse()->getFachbereich();

            return [
                'id' => $exercise->getId(),
                'name' => $exercise->getName(),
                'fachbereich' => $fachbereich ?
                    [
                        'id' => $fachbereich->getId(),
                        'name' => $fachbereich->getName(),
                    ] : null,
                'course' => [
                    'id' => $exercise->getCourse()->getId(),
                    'name' => $exercise->getCourse()->getName(),
                ],
                'status' => $this->exerciseService->getExerciseStatusForUser($exercise, $user)->value,
                'phaseCount' => $exercise->getPhases()->count(),
                'completedPhases' => $exercise->getPhases()
                    ->filter(
                        fn(ExercisePhase $exercisePhase) => $this->exercisePhaseService->getStatusForUser($exercisePhase, $user) === ExercisePhaseStatus::BEENDET
                    )
                    ->count(),
                'lastEditedAt' => $this->exerciseService->getLastEditDateByUser($exercise, $user)
            ];
        }, $exercisesCopy);
    }

    public function getVideoFavoritesResponse(): array
    {
        $user = $this->userService->getLoggendInUser();

        $videoFavorites = $this->videoFavouritesService->getFavouriteVideosForUser($user);

        $response = array_reduce($videoFavorites, function ($acc, VideoFavorite $videoFavorite) use ($user) {
            $video = $videoFavorite->getVideo();

            $clientSideVideo = $video->getAsClientSideVideo($this->appRuntime);

            return [...$acc, [
                'id' => $videoFavorite->getId(),
                'video' => array_merge(
                    $clientSideVideo->toArray(),
                    [
                        'userIsCreator' => $user === $video->getCreator(),
                        'courses' => $video->getCourses()->map(fn(Course $course) => [
                            'id' => $course->getId(),
                            'name' => $course->getName(),
                        ])->toArray(),
                        'fachbereiche' => $video->getCourses()->map(function (Course $course) {
                            return $course->getFachbereich() ? [
                                'id' => $course->getFachbereich()->getId(),
                                'name' => $course->getFachbereich()->getName(),
                            ] : null;
                        })->filter(fn(array|null $course) => !is_null($course))->toArray(),
                    ]
                )
            ]];
        }, []);

        return $response;
    }

    public function getMaterialResponse(): array
    {
        $user = $this->userService->getLoggendInUser();
        $materialList = $this->materialService->getMaterialsForUser($user);

        $materialListCopy = [...$materialList];

        usort($materialListCopy, function (Material $materialA, Material $materialB) {
            if (is_null($materialA->getLastUpdatedAt()) && is_null($materialB->getLastUpdatedAt())) {
                return $this->sortByDateTimeImmutable($materialA->getCreatedAt(), $materialB->getCreatedAt());
            } elseif (is_null($materialA->getLastUpdatedAt()) && !is_null($materialB->getLastUpdatedAt())) {
                return -1;
            } elseif (!is_null($materialA->getLastUpdatedAt()) && is_null(($materialB->getLastUpdatedAt()))) {
                return 1;
            } else {
                return $this->sortByDateTimeImmutable($materialA->getLastUpdatedAt(), $materialB->getLastUpdatedAt());
            }
        });

        return array_map(function (Material $material) {
            $originalExercisePhaseTeam = $material->getOriginalPhaseTeam();

            $originalExercisePhase = $originalExercisePhaseTeam?->getExercisePhase();
            $originalExercise = $originalExercisePhase?->getBelongsToExercise();
            $course = $material->getOriginalPhaseTeam()?->getExercisePhase()->getBelongsToExercise()->getCourse();
            $fachbereich = $material->getOriginalPhaseTeam()?->getExercisePhase()->getBelongsToExercise()->getCourse()->getFachbereich();

            return [
                'id' => $material->getId(),
                'material' => $material->getMaterial(),
                'owner' => $material->getOwner()->getUsername(),
                "name" => $material->getName(),
                'originalExercisePhaseTeamId' => $originalExercisePhaseTeam?->getId(),
                'originalExercisePhaseName' => $originalExercisePhase?->getName(),
                'originalExercisePhaseUrl' => $originalExercisePhaseTeam ? $this->router->generate(
                    "exercise__show-phase",
                    [
                        "id" => $originalExercise->getId(),
                        "phaseId" => $originalExercisePhase->getId()
                    ]
                ) : null,
                'createdAt' => $material->getCreatedAt(),
                'lastUpdatedAt' => $material->getLastUpdatedAt(),
                'fachbereich' => $fachbereich ? [
                    'id' => $fachbereich->getId(),
                    'name' => $fachbereich->getName(),
                ] : null,
                'course' => $course ? [
                    'id' => $course->getId(),
                    'name' => $course->getName(),
                ] : null,
            ];
        }, $materialListCopy);
    }

    public function getFachbereicheResponse(User $user): array
    {
        $result = [];
        /** @var Fachbereich[] $fachbereiche */
        $fachbereiche = $user->getCourseRoles()->map(
            fn(CourseRole $courseRole) => $courseRole->getCourse()->getFachbereich()
        )->toArray();

        foreach ($fachbereiche as $fachbereich) {
            if (is_null($fachbereich)) {
                continue;
            }

            $result[$fachbereich->getId()] = [
                'id' => $fachbereich->getId(),
                'name' => $fachbereich->getName(),
            ];
        }

        return $result;
    }

    public function getCoursesResponse(User $user): array
    {
        /** @var Course[] $courses */
        $courses = $user->getCourseRoles()->map(fn(CourseRole $courseRole) => $courseRole->getCourse())->toArray();
        $result = [];

        foreach ($courses as $course) {
            $result[$course->getId()] = [
                'id' => $course->getId(),
                'name' => $course->getName(),
            ];
        }

        return $result;
    }

    private function sortByExerciseStatus(ExerciseStatus $statusA, ExerciseStatus $statusB): int
    {
        // NEU < IN_BEARBEITUNG < BEENDET
        if ($statusA === ExerciseStatus::NEU && $statusB !== ExerciseStatus::NEU) {
            return -1;
        } elseif ($statusA !== ExerciseStatus::NEU && $statusB === ExerciseStatus::NEU) {
            return 1;
        } elseif ($statusA === ExerciseStatus::IN_BEARBEITUNG && $statusB === ExerciseStatus::BEENDET) {
            return -1;
        } elseif ($statusB === ExerciseStatus::IN_BEARBEITUNG && $statusA === ExerciseStatus::BEENDET) {
            return 1;
        } else {
            return 0;
        }
    }

    private function sortByDateTimeImmutable(?DateTimeImmutable $dateA, ?DateTimeImmutable $dateB): int
    {
        if ($dateA === $dateB) {
            return 0;
        } else {
            return $dateA < $dateB
                ? 1
                : -1;
        }
    }
}
