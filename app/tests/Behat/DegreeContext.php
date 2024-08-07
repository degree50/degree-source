<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use App\DataExport\Dto\TextFileDto;
use App\DataExport\Service\DegreeDataToCsvService;
use App\Domain\CourseRole\Model\CourseRole;
use App\Domain\CutVideo\Repository\CutVideoRepository;
use App\Domain\Exercise\Model\Exercise;
use App\Domain\Exercise\Model\ExerciseStatus;
use App\Domain\Exercise\Repository\ExerciseRepository;
use App\Domain\Exercise\Service\ExerciseService;
use App\Domain\ExercisePhase\Model\ExercisePhase;
use App\Domain\ExercisePhase\Model\ExercisePhaseStatus;
use App\Domain\ExercisePhase\Model\ExercisePhaseType;
use App\Domain\ExercisePhase\Model\MaterialPhase;
use App\Domain\ExercisePhase\Repository\ExercisePhaseRepository;
use App\Domain\ExercisePhase\Service\ExercisePhaseService;
use App\Domain\ExercisePhaseTeam\Model\ExercisePhaseTeam;
use App\Domain\ExercisePhaseTeam\Repository\ExercisePhaseTeamRepository;
use App\Domain\Fachbereich\Model\Fachbereich;
use App\Domain\Material\Model\Material;
use App\Domain\Material\Repository\MaterialRepository;
use App\Domain\Solution\Model\Solution;
use App\Domain\Solution\Repository\SolutionRepository;
use App\Domain\Solution\Service\SolutionService;
use App\Domain\User\Model\User;
use App\Domain\User\Repository\UserRepository;
use App\Domain\User\Service\UserExpirationService;
use App\Domain\User\Service\UserMaterialService;
use App\Domain\User\Service\UserService;
use App\Domain\Video\Model\Video;
use App\Domain\Video\Repository\VideoRepository;
use App\Domain\Video\Service\VideoService;
use App\Domain\VideoFavorite\Model\VideoFavorite;
use App\Domain\VideoFavorite\Service\VideoFavouritesService;
use App\Domain\VirtualizedFile\Model\VirtualizedFile;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Doctrine\ORM\EntityManagerInterface;
use Sandstorm\E2ETestTools\Tests\Behavior\Bootstrap\PlaywrightTrait;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use function PHPUnit\Framework\assertContains;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertTrue;

require_once(__DIR__ . '/AttachmentContextTrait.php');
require_once(__DIR__ . '/CourseContextTrait.php');
require_once(__DIR__ . '/CsvContextTrait.php');
require_once(__DIR__ . '/ExerciseContextTrait.php');
require_once(__DIR__ . '/ExercisePhaseContextTrait.php');
require_once(__DIR__ . '/PlaywrightContextTrait.php');
require_once(__DIR__ . '/UserContextTrait.php');
require_once(__DIR__ . '/VideoContextTrait.php');

/**
 * This context class contains the definitions of the steps used by the demo
 * feature file. Learn how to get started with Behat and BDD on Behat's website.
 *
 * @see http://behat.org/en/latest/quick_start.html
 */
final class DegreeContext implements Context
{
    use DatabaseFixtureContextTrait;
    use PlaywrightTrait;
    use EmailTrait;

    /*
     * Sub-contexts
     */
    use AttachmentContextTrait;
    use CourseContextTrait;
    use CsvContextTrait;
    use ExerciseContextTrait;
    use ExercisePhaseContextTrait;
    use PlaywrightContextTrait;
    use UserContextTrait;
    use VideoContextTrait;

    private ?string $clientSideJSON;

    /** @var TextFileDto[] $csvDtoList */
    private ?array $csvDtoList;

    private ?array $queryResult;
    private ?ExercisePhase $currentExercisePhase = null;
    private ?Exercise $currentExercise = null;
    private ?User $currentUser = null;

    const string TEST_STUDENT = "test-student@sandstorm.de";
    const string TEST_DOZENT = "test-dozent@sandstorm.de";
    const string TEST_COURSE_1 = "course1";
    const string TEST_COURSE_2 = "course2";
    const string TEST_EXERCISE_1 = "exercise1";
    const string TEST_EXERCISE_2 = "exercise2";
    const string TEST_EXERCISE_3 = "exercise3";
    const string TEST_EXERCISE_4 = "exercise4";
    const string ANALYSIS_PHASE_BASE_ID = 'analysis1';
    const string CUT_PHASE_BASE_ID = 'cut1';
    const string REFLEXION_PHASE_BASE_ID = 'reflexion1';
    const string MATERIAL_PHASE_BASE_ID = 'material1';
    const string GROUP_PHASE_BASE_ID = 'groupPhase1';
    const string TEST_FACHBEREICH_1 = 'fachbereich1';

    public function __construct(
        private readonly RouterInterface $router,
        private readonly EntityManagerInterface $entityManager,
        private readonly KernelInterface $kernel,
        private readonly Security $security,
        private readonly SolutionService $solutionService,
        private readonly DegreeDataToCsvService $degreeDataToCsvService,
        private readonly UserService $userService,
        private readonly UserExpirationService $userExpirationService,
        private readonly VideoService $videoService,
        private readonly ExerciseService $exerciseService,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly ExercisePhaseRepository $exercisePhaseRepository,
        private readonly UserRepository $userRepository,
        private readonly ExerciseRepository $exerciseRepository,
        private readonly ExercisePhaseTeamRepository $exercisePhaseTeamRepository,
        private readonly ExercisePhaseService $exercisePhaseService,
        private readonly VideoFavouritesService $videoFavouritesService,
        private readonly UserMaterialService $materialService,
        private readonly MaterialRepository $materialRepository,
        private readonly VideoRepository $videoRepository,
        private readonly SolutionRepository $solutionRepository,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly CutVideoRepository $cutVideoRepository,
    ) {
        $this->setupPlaywright();
    }


    /**
     * @Given I am logged in as :username
     */
    public function iAmLoggedInAs($username): void
    {
        $user = $this->getUserByEmail($username);
        if (!$user) {
            $user = $this->createUser($username);
        }

        $this->tokenStorage->setToken(new UsernamePasswordToken($user, 'main', ['ROLE_USER']));
        $this->tokenStorage->getToken()->setUser($user);

        $this->currentUser = $user;
    }

    /**
     * @Given I am not logged in
     */
    public function iAmNotLoggedIn(): void
    {
        $this->tokenStorage->setToken(null);
    }

    private function createExercisePhaseId(string $exerciseId, string $courseId, string $baseId): string
    {
        return $exerciseId . '_' . $courseId . '_' . $baseId;
    }

    private function createPhaseOfTypeInExercise(
        string $phaseId,
        string $exerciseId,
        ExercisePhaseType $exercisePhaseType,
        bool $isGroupPhase = false,
        ?string $dependsOnPhase = null
    ): ExercisePhase {
        if ($dependsOnPhase === "") {
            $dependsOnPhase = null;
        }
        return $this->createExercisePhase([
            "type" => $exercisePhaseType->value,
            "id" => $phaseId,
            "name" => $phaseId,
            "task" => "description of analysis1",
            "isGroupPhase" => $isGroupPhase,
            "sorting" => 0,
            "otherSolutionsAreAccessible" => true,
            "belongsToExercise" => $exerciseId,
            "dependsOnPhase" => $dependsOnPhase,
            "videoAnnotationsActive" => true,
            "videoCodesActive" => true
        ]);
    }

    /**
     * Create an exercise with the given ID and adds a course role "DOZENT" for a mocked dozent
     */
    private function createExerciseInCourseByUser(string $exerciseId, string $courseId, User $user): void
    {
        // ensure user has course role dozent for the given course
        $this->userHasCourseRole($user, CourseRole::DOZENT, $courseId);
        $this->ensureExerciseByUserInCourseExists($exerciseId, $user, $courseId);
    }

    /**
     * Creates an exercise with the given ID and adds a course role "DOZENT" for a mocked dozent
     *
     * @Given an exercise with id :id in course :courseId exists
     */
    public function anExerciseWithIdExistsInCourse(string $id, string $courseId): void
    {
        $testDozent = $this->createUser(self::TEST_DOZENT);
        $this->createExerciseInCourseByUser($id, $courseId, $testDozent);
    }

    /**
     * @Given The exercise :exerciseId has these phases:
     */
    public function theExerciseHasThesePhases(string $exerciseId, TableNode $phases): void
    {
        foreach ($phases as $_key => $phase) {
            $phaseId = $phase['id'];
            $type = ExercisePhaseType::from($phase['type']);
            $isGroupPhase = $phase['isGroupPhase'] === 'yes';
            $dependsOnPhase = $phase['dependsOn'] ?? null;

            $this->createPhaseOfTypeInExercise(
                $phaseId,
                $exerciseId,
                $type,
                $isGroupPhase,
                $dependsOnPhase
            );
        }
    }
    /**
     * @Given I am a student working on :exerciseId
     * */
    public function iAmAStudentWorkingOnExercise($exerciseId): void
    {
        $this->createUser(self::TEST_STUDENT, null, false, true);
        $exercise = $this->exerciseRepository->find($exerciseId);
        $course = $exercise->getCourse();
        $this->userHasCourseRole(self::TEST_STUDENT, CourseRole::STUDENT, $course->getId());

        // Log in as the actual user we want to progress further with
        $this->iAmLoggedInAs(self::TEST_STUDENT);
    }

    /**
     * @When I have not yet started an exercise phase of :exerciseId
     */
    public function iHaveNotYetStartedAnExercisePhase($exerciseId): void
    {
        $this->assertExercisePhaseTeamsCreatedByUserDoNotExist(self::TEST_STUDENT);
    }

    /**
     * @Then The derived exercise phase status of :phaseId should be :phaseStatus for the student
     */
    public function theDerivedExercisePhaseStatusOfShouldBe(string $phaseId, string $phaseStatus): void
    {
        $user = $this->userRepository->find(self::TEST_STUDENT);
        $exercisePhase = $this->exercisePhaseRepository->find($phaseId);

        $actualExercisePhaseStatus = $this->exercisePhaseService->getStatusForUser($exercisePhase, $user);
        assertEquals($phaseStatus, $actualExercisePhaseStatus->value);
    }
    /**
     * @Then The derived exercise phase status of the first phase should be :exercisePhaseStatus
     */
    public function theDerivedExercisePhaseStatusOfTheFirstPhaseShouldBe(string $exercisePhaseStatus): void
    {
        $exercise = $this->exerciseRepository->find(self::TEST_EXERCISE_1);
        $exercisePhase = $this->exercisePhaseRepository->findFirstExercisePhase($exercise);
        $user = $this->userRepository->find(self::TEST_STUDENT);

        $actualExercisePhaseStatus = $this->exercisePhaseService->getStatusForUser($exercisePhase, $user);
        assertEquals($exercisePhaseStatus, $actualExercisePhaseStatus->value);
    }

    /**
     * @Then The derived exercise phase status of the group phase should be :exercisePhaseStatus
     */
    public function theDerivedExercisePhaseStatusOfTheGroupPhaseShouldBe(string $exercisePhaseStatus): void
    {
        $exercisePhase = $this->exercisePhaseRepository->find($this->createExercisePhaseId(self::TEST_EXERCISE_1, self::TEST_COURSE_1, self::GROUP_PHASE_BASE_ID));
        $user = $this->userRepository->find(self::TEST_STUDENT);

        $actualExercisePhaseStatus = $this->exercisePhaseService->getStatusForUser($exercisePhase, $user);
        assertEquals($exercisePhaseStatus, $actualExercisePhaseStatus->value);
    }

    /**
     * @When I am not part of a group for a group exercise phase
     */
    public function iAmNotPartOfAGroupForAGroupExercisePhase(): void
    {
        $this->assertExercisePhaseTeamsCreatedByUserDoNotExist(self::TEST_STUDENT);
    }

    /**
     * @Then My exercise phase status of the group phase should be :exercisePhaseStatus
     */
    public function myExercisePhaseStatusOfTheGroupPhaseShouldBe(string $exercisePhaseStatus): void
    {
        $user = $this->userRepository->find(self::TEST_STUDENT);
        $exercisePhase = $this->exercisePhaseRepository->find($this->createExercisePhaseId(self::TEST_EXERCISE_1, self::TEST_COURSE_1, self::GROUP_PHASE_BASE_ID));

        assertEquals($exercisePhaseStatus, $this->exercisePhaseService->getStatusForUser($exercisePhase, $user)->value);
    }

    /**
     * @Then The students exercise phase status of :phaseId should be :status
     */
    public function theStudentsExercisePhaseStatusOfShouldBe(string $phaseId, string $phaseStatus): void
    {
        $user = $this->userRepository->find(self::TEST_STUDENT);
        $exercisePhase = $this->exercisePhaseRepository->find($phaseId);
        assertEquals($phaseStatus, $this->exercisePhaseService->getStatusForUser($exercisePhase, $user)->value);
    }

    /**
     * @When I enter a group in :phaseId
     */
    public function iEnterAGroupIn($phaseId): void
    {
        $exercisePhase = $this->exercisePhaseRepository->find($phaseId);
        $user = $this->userRepository->find(self::TEST_STUDENT);

        $exercisePhaseTeam = $this->exercisePhaseService->createPhaseTeam($exercisePhase, $user);
        $this->exercisePhaseService->addMemberToPhaseTeam($exercisePhaseTeam, $user);
    }

    /**
     * @Then The phase status of :phaseId should be :status
     */
    public function thePhaseStatusOfPhaseShouldBe($phaseId, $status): void
    {
        $user = $this->userRepository->find(self::TEST_STUDENT);
        $exercisePhase = $this->exercisePhaseRepository->find($phaseId);

        assertEquals($status, $this->exercisePhaseService->getStatusForUser($exercisePhase, $user)->value);
    }

    /**
     * @Then The exercise phase status should be :exercisePhaseStatus
     */
    public function theExercisePhaseStatusShouldBe(string $exercisePhaseStatus): void
    {
        $user = $this->userRepository->find(self::TEST_STUDENT);
        $exercisePhase = $this->currentExercisePhase;

        assertEquals($exercisePhaseStatus, $this->exercisePhaseService->getStatusForUser($exercisePhase, $user)->value);
    }

    /**
     * @When I start :phaseId for the first time
     */
    public function iStartAnExercisePhaseForTheFirstTime(string $phaseId): ?ExercisePhase
    {
        $exercisePhase = $this->exercisePhaseRepository->find($phaseId);
        $user = $this->userRepository->find(self::TEST_STUDENT);

        $exercisePhaseTeam = $this->exercisePhaseService->createPhaseTeam($exercisePhase, $user);
        $this->exercisePhaseService->addMemberToPhaseTeam($exercisePhaseTeam, $user);

        $this->currentExercisePhase = $exercisePhase;
        return $exercisePhase;
    }

    /**
     * @When I finish phase :phaseId
     */
    public function iFinishTheExercisePhase(string $phaseId): void
    {
        $exercisePhase = $this->exercisePhaseRepository->find($phaseId);
        $user = $this->userRepository->find(self::TEST_STUDENT);

        $exercisePhaseTeam = $this->exercisePhaseTeamRepository->findByMemberAndExercisePhase($user, $exercisePhase);

        $this->createDummySolution($exercisePhaseTeam, 'solution1', '<p>Material</p>');

        $this->exercisePhaseService->finishPhase($exercisePhaseTeam);
    }

    /**
     * @Given I am working on a phase :phaseId where a review is required: :reviewIsRequired
     */
    public function iAmWorkingOnAPhaseThatHasTheReviewState(string $phaseId, string $reviewIsRequired): void
    {
        $exercisePhase = $this->exercisePhaseRepository->find($phaseId);

        $user = $this->userRepository->find(self::TEST_STUDENT);

        $exercisePhaseTeam = $this->exercisePhaseService->createPhaseTeam($exercisePhase, $user);
        $this->exercisePhaseService->addMemberToPhaseTeam($exercisePhaseTeam, $user);


        if ($exercisePhase instanceof MaterialPhase) {
            $reviewRequired = $reviewIsRequired === 'yes';
            $exercisePhase->setReviewRequired($reviewRequired);

            $this->entityManager->persist($exercisePhase);
        }

        $this->entityManager->persist($exercisePhaseTeam);
        $this->entityManager->flush();

        // WHY: Persist current ExercisePhase that the user is working on in context
        $this->currentExercisePhase = $exercisePhase;
    }

    /**
     * @Given I am a dozent in course :courseId
     */
    public function iAmADozentInCourse($courseId): void
    {
        $dozent = $this->userRepository->find(self::TEST_DOZENT);
        if (is_null($dozent)) {
            $this->createUser(self::TEST_DOZENT);
            $this->userHasCourseRole(self::TEST_DOZENT, CourseRole::DOZENT, $courseId);
        }
    }

    /**
     * @When I finish the review of a solution of a material phase :phaseId
     **/
    public function iFinishTheReviewOfAMaterialPhase($phaseId): void
    {
        $phase = $this->exercisePhaseRepository->find($phaseId);

        if ($phase instanceof MaterialPhase) {
            $student = $this->userRepository->find(self::TEST_STUDENT);
            $exercisePhaseTeam = $this->exercisePhaseTeamRepository->findByMemberAndExercisePhase($student, $phase);
            $this->exercisePhaseService->finishPhase($exercisePhaseTeam);
            $this->exercisePhaseService->finishReview($exercisePhaseTeam);
        }
    }

    /**
     * @When I open an exercise phase :phaseId with status :phaseStatus
     */
    public function iOpenAnExercisePhaseWithStatus($phaseId, $phaseStatus): void
    {
        $status = ExercisePhaseStatus::tryFrom($phaseStatus);

        $exercisePhase = $this->iStartAnExercisePhaseForTheFirstTime($phaseId);

        $user = $this->userRepository->find(self::TEST_STUDENT);

        // state setzen
        $exercisePhaseTeam = $this->exercisePhaseTeamRepository->findByMemberAndExercisePhase($user, $exercisePhase);
        $exercisePhaseTeam->setStatus($status);

        // open
        $this->exercisePhaseService->openPhase($exercisePhaseTeam);
    }

    /**
     * @Then The derived exercise status of :exerciseId should be :exerciseStatus
     */
    public function theDerivedExerciseStatusShouldBe($exerciseId, string $exerciseStatus): void
    {
        $status = ExerciseStatus::tryFrom($exerciseStatus);
        $user = $this->userRepository->find(self::TEST_STUDENT);
        $exercise = $this->exerciseRepository->find($exerciseId);

        assertEquals($status, $this->exerciseService->getExerciseStatusForUser($exercise, $user));
    }

    private function finishPhase(ExercisePhase $exercisePhase, User $user): void
    {
        $exercisePhaseTeam = new ExercisePhaseTeam();
        $exercisePhaseTeam->setExercisePhase($exercisePhase);
        $exercisePhaseTeam->addMember($user);
        $exercisePhaseTeam->setCreator($user);

        $this->entityManager->persist($exercisePhaseTeam);
        $this->entityManager->flush();

        $this->exercisePhaseService->finishPhase($exercisePhaseTeam);
    }

    /**
     * @When I have finished all phases of exercise :exerciseId
     */
    public function iHaveFinishedAllPhasesOfAnExercise($exerciseId): void
    {
        $student = $this->userRepository->find(self::TEST_STUDENT);
        $exercise = $this->exerciseRepository->find($exerciseId);

        foreach ($exercise->getPhases() as $_key => $phase) {
            $this->finishPhase($phase, $student);
        }
    }

    /**
     * @Given I am a student logged in via browser
     */
    public function iAmStudentLoggedInViaBrowser(): void
    {
        $this->currentUser = $this->createUser(self::TEST_STUDENT, null, true, true);

        $this->iAmLoggedInViaBrowserAs(self::TEST_STUDENT);
    }

    private function ensureAnExerciseTeamForUserAndPhaseExists(User $user, ExercisePhase $exercisePhase): ExercisePhaseTeam
    {
        $exercisePhaseTeam = new ExercisePhaseTeam();
        $exercisePhaseTeam->setExercisePhase($exercisePhase);
        $exercisePhaseTeam->addMember($user);
        $exercisePhaseTeam->setCreator($user);

        $this->entityManager->persist($exercisePhaseTeam);
        $this->entityManager->flush();

        return $exercisePhaseTeam;
    }

    /**
     * @Given I am part of two courses with multiple exercises
     */
    public function iAmPartOfTwoCoursesWithMultipleExercises(): void
    {
        // WHY: we have to set logged in user to dozent to create exercises
        // so we need to set it to the previously logged in user afterwards
        $previouslyLoggedInUser = $this->currentUser;

        $testDozent = $this->createUser(self::TEST_DOZENT);

        // course 1
        $course = $this->ensureCourseExists(self::TEST_COURSE_1);
        $this->userHasCourseRole($previouslyLoggedInUser, CourseRole::STUDENT, self::TEST_COURSE_1);

        // add course to fachbereich
        $fachbereich = $this->ensureFachbereichExists(self::TEST_FACHBEREICH_1);
        $course->setFachbereich($fachbereich);

        // exercise 1
        $this->createExerciseInCourseByUser(self::TEST_EXERCISE_1, self::TEST_COURSE_1, $testDozent);

        // Create phases
        $this->createPhaseOfTypeInExercise(
            $this->createExercisePhaseId(self::TEST_EXERCISE_1, self::TEST_COURSE_1, self::ANALYSIS_PHASE_BASE_ID),
            self::TEST_EXERCISE_1,
            ExercisePhaseType::VIDEO_ANALYSIS
        );

        $this->createPhaseOfTypeInExercise(
            $this->createExercisePhaseId(self::TEST_EXERCISE_1, self::TEST_COURSE_1, self::CUT_PHASE_BASE_ID),
            self::TEST_EXERCISE_1,
            ExercisePhaseType::VIDEO_CUT
        );

        $this->createPhaseOfTypeInExercise(
            $this->createExercisePhaseId(self::TEST_EXERCISE_1, self::TEST_COURSE_1, self::GROUP_PHASE_BASE_ID),
            self::TEST_EXERCISE_1,
            ExercisePhaseType::VIDEO_CUT,
            true
        );

        $this->createPhaseOfTypeInExercise(
            $this->createExercisePhaseId(self::TEST_EXERCISE_1, self::TEST_COURSE_1, self::REFLEXION_PHASE_BASE_ID),
            self::TEST_EXERCISE_1,
            ExercisePhaseType::REFLEXION
        );

        $this->createPhaseOfTypeInExercise(
            $this->createExercisePhaseId(self::TEST_EXERCISE_1, self::TEST_COURSE_1, self::MATERIAL_PHASE_BASE_ID),
            self::TEST_EXERCISE_1,
            ExercisePhaseType::MATERIAL
        );

        // exercise 2
        $this->createExerciseInCourseByUser(self::TEST_EXERCISE_2, self::TEST_COURSE_1, $testDozent);

        $this->createPhaseOfTypeInExercise(
            $this->createExercisePhaseId(self::TEST_EXERCISE_2, self::TEST_COURSE_1, self::ANALYSIS_PHASE_BASE_ID),
            self::TEST_EXERCISE_2,
            ExercisePhaseType::VIDEO_ANALYSIS
        );

        $this->createPhaseOfTypeInExercise(
            $this->createExercisePhaseId(self::TEST_EXERCISE_2, self::TEST_COURSE_1, self::CUT_PHASE_BASE_ID),
            self::TEST_EXERCISE_2,
            ExercisePhaseType::VIDEO_CUT
        );

        $this->createPhaseOfTypeInExercise(
            $this->createExercisePhaseId(self::TEST_EXERCISE_2, self::TEST_COURSE_1, self::GROUP_PHASE_BASE_ID),
            self::TEST_EXERCISE_2,
            ExercisePhaseType::VIDEO_CUT,
            true
        );

        $this->createPhaseOfTypeInExercise(
            $this->createExercisePhaseId(self::TEST_EXERCISE_2, self::TEST_COURSE_1, self::REFLEXION_PHASE_BASE_ID),
            self::TEST_EXERCISE_2,
            ExercisePhaseType::REFLEXION
        );

        $this->createPhaseOfTypeInExercise(
            $this->createExercisePhaseId(self::TEST_EXERCISE_2, self::TEST_COURSE_1, self::MATERIAL_PHASE_BASE_ID),
            self::TEST_EXERCISE_2,
            ExercisePhaseType::MATERIAL
        );

        // course 2
        $this->ensureCourseExists(self::TEST_COURSE_2);
        $this->userHasCourseRole($previouslyLoggedInUser, CourseRole::STUDENT, self::TEST_COURSE_2);

        // exercise 1
        $this->createExerciseInCourseByUser(self::TEST_EXERCISE_3, self::TEST_COURSE_2, $testDozent);

        // Create phases
        $this->createPhaseOfTypeInExercise(
            $this->createExercisePhaseId(self::TEST_EXERCISE_3, self::TEST_COURSE_2, self::ANALYSIS_PHASE_BASE_ID),
            self::TEST_EXERCISE_3,
            ExercisePhaseType::VIDEO_ANALYSIS
        );

        $this->createPhaseOfTypeInExercise(
            $this->createExercisePhaseId(self::TEST_EXERCISE_3, self::TEST_COURSE_2, self::CUT_PHASE_BASE_ID),
            self::TEST_EXERCISE_3,
            ExercisePhaseType::VIDEO_CUT
        );

        $this->createPhaseOfTypeInExercise(
            $this->createExercisePhaseId(self::TEST_EXERCISE_3, self::TEST_COURSE_2, self::GROUP_PHASE_BASE_ID),
            self::TEST_EXERCISE_3,
            ExercisePhaseType::VIDEO_CUT,
            true
        );

        $this->createPhaseOfTypeInExercise(
            $this->createExercisePhaseId(self::TEST_EXERCISE_3, self::TEST_COURSE_2, self::REFLEXION_PHASE_BASE_ID),
            self::TEST_EXERCISE_3,
            ExercisePhaseType::REFLEXION
        );

        $this->createPhaseOfTypeInExercise(
            $this->createExercisePhaseId(self::TEST_EXERCISE_3, self::TEST_COURSE_2, self::MATERIAL_PHASE_BASE_ID),
            self::TEST_EXERCISE_3,
            ExercisePhaseType::MATERIAL
        );

        // exercise 2
        $this->createExerciseInCourseByUser(self::TEST_EXERCISE_4, self::TEST_COURSE_2, $testDozent);

        $this->createPhaseOfTypeInExercise(
            $this->createExercisePhaseId(self::TEST_EXERCISE_4, self::TEST_COURSE_2, self::ANALYSIS_PHASE_BASE_ID),
            self::TEST_EXERCISE_4,
            ExercisePhaseType::VIDEO_ANALYSIS
        );

        $this->createPhaseOfTypeInExercise(
            $this->createExercisePhaseId(self::TEST_EXERCISE_4, self::TEST_COURSE_2, self::CUT_PHASE_BASE_ID),
            self::TEST_EXERCISE_4,
            ExercisePhaseType::VIDEO_CUT
        );

        $this->createPhaseOfTypeInExercise(
            $this->createExercisePhaseId(self::TEST_EXERCISE_4, self::TEST_COURSE_2, self::GROUP_PHASE_BASE_ID),
            self::TEST_EXERCISE_4,
            ExercisePhaseType::VIDEO_CUT,
            true
        );

        $this->createPhaseOfTypeInExercise(
            $this->createExercisePhaseId(self::TEST_EXERCISE_4, self::TEST_COURSE_2, self::REFLEXION_PHASE_BASE_ID),
            self::TEST_EXERCISE_4,
            ExercisePhaseType::REFLEXION
        );

        $this->createPhaseOfTypeInExercise(
            $this->createExercisePhaseId(self::TEST_EXERCISE_4, self::TEST_COURSE_2, self::MATERIAL_PHASE_BASE_ID),
            self::TEST_EXERCISE_4,
            ExercisePhaseType::MATERIAL
        );

        $this->currentUser = $previouslyLoggedInUser;
    }

    /**
     * @When I navigate to "Schreibtisch"
     */
    public function iNavigateToSchreibtisch(): void
    {
        $this->iClickOnFirstElementWithTestId('header-menu-schreibtisch');
    }

    /**
     * @Then I get access to "Meine Aufgaben", "Meine Videofavoriten", "Meine Materialien"
     */
    public function iGetAccessToSchreibtisch(): void
    {
        $this->waitForSelector('schreibtisch-navigation');
        $this->pageContainsTexts(["Meine Aufgaben", "Meine Videofavoriten", "Meine Materialien"]);
    }

    /**
     * @Then I see my available exercises by default
     */
    public function iSeeMyAvailableExercisesByDefault(): void
    {
        $this->waitForSelector('exercise-list');
        $this->pageContainsTexts([
            self::TEST_EXERCISE_1,
            self::TEST_EXERCISE_2,
            self::TEST_EXERCISE_3,
            self::TEST_EXERCISE_4,
        ]);
    }

    /**
     * @Given I am a student
     */
    public function iAmAStudent(): void
    {
        $this->iAmLoggedInAs(self::TEST_STUDENT);
    }

    /**
     * @When I access "Meine Aufgaben"
     */
    public function iAccessMeineAufgaben(): void
    {
        $this->queryResult = $this->exerciseService->getExercisesForUser($this->currentUser);
    }

    /**
     * @When I access "Meine Videofavoriten"
     */
    public function iAccessMeineVideofavoriten(): void
    {
        $videoFavorites = $this->videoFavouritesService->getFavouriteVideosForUser($this->currentUser);
        $videos = array_map(fn (VideoFavorite $videoFavourite) => $videoFavourite->getVideo(), $videoFavorites);

        $this->queryResult = $videos;
    }

    /**
     * @Then All my available exercises from both courses are shown
     */
    public function allMyAvailableExercisesFromBothCoursesAreShown(): void
    {
        $results = array_map(fn (Exercise $exercise) => $exercise->getId(), $this->queryResult);
        $expected = [
            self::TEST_EXERCISE_1,
            self::TEST_EXERCISE_2,
            self::TEST_EXERCISE_3,
            self::TEST_EXERCISE_4
        ];

        foreach ($expected as $phaseId) {
            assertContains($phaseId, $results);
        }
    }

    /**
     * @Given I have some video favorites
     */
    public function iHaveSomeVideoFavorites(): void
    {
        $videos = $this->createExampleVideos();

        $this->videoFavouritesService->addVideoFavouriteForUser($videos[0], $this->currentUser);
        $this->videoFavouritesService->addVideoFavouriteForUser($videos[1], $this->currentUser);
    }

    /**
     * @Then All my favorite videos are shown
     */
    public function allMyFavoriteVideosAreShown(): void
    {
        /** @var Video[] $result */
        $result = array_map(fn (Video $video) => $video->getId(), $this->queryResult);
        $expected = ["video1", "video2"];

        foreach ($expected as $videoId) {
            assertContains($videoId, $result);
        }
    }

    private function createExampleVideos(): array
    {
        $actualCurrentUser = $this->currentUser;

        $course = $this->ensureCourseExists(self::TEST_COURSE_1);
        $this->userHasCourseRole(
            $this->currentUser->getId(),
            $this->currentUser->isStudent() ? CourseRole::STUDENT : CourseRole::DOZENT,
            self::TEST_COURSE_1
        );

        // Temporarily switch to dozent user to create videos
        $dozent = $this->createUser(self::TEST_DOZENT);
        $this->userHasCourseRole(self::TEST_DOZENT, CourseRole::DOZENT, self::TEST_COURSE_1);

        $videoIdList = ["video1", "video2", "video3"];

        $videos = array_map(function ($videoId) use ($dozent, $course) {
            $video = new Video($videoId);
            $video->addCourse($course);
            $this->videoService->persistUploadedVideoFile(
                $video,
                VirtualizedFile::fromString('test'),
                $dozent
            );

            return $video;
        }, $videoIdList);

        $this->currentUser = $actualCurrentUser;

        return $videos;
    }

    /**
     * @When I add a video to my favorite videos
     */
    public function iAddAVideoToMyFavoriteVideos(): void
    {
        // add other video favorites
        $this->iHaveSomeVideoFavorites();

        assertTrue($this->currentUser->isStudent(), "User is not a student!");

        // navigate to video page in Mediathek
        $this->iClickOnFirstElementWithTestId('header-menu-mediathek');
        // click "zu Favoriten hinzufügen" button
        $this->iClickOnFirstElementWithTestId('tile__favor-button-video3');
    }

    /**
     * @When I Navigate to "Meine Videofavoriten" on the "Schreibtisch"
     */
    public function iNavigateToMeineVideofavoritenOnTheSchreibtisch(): void
    {
        $this->iNavigateToSchreibtisch();
        $this->iClickOn('Meine Videofavoriten');

        // Because we have an async request for favorites going on that might not
        // yet have resolved, we need to wait until our loading spinner is no longer shown
        $this->waitForSelector('video-favorites');
    }

    /**
     * @Then I see all my favorite videos
     */
    public function iSeeAllMyFavoriteVideos(): void
    {
        $this->pageContainsTexts([
            'video1',
            'video2',
            'video3',
        ]);
    }

    /**
     * @When I remove a video from my favorites
     */
    public function iRemoveAVideoFromMyFavorites(): void
    {

        assertTrue($this->currentUser->isStudent(), "User is not a student!");

        // navigate to video page in Mediathek
        $this->iClickOnFirstElementWithTestId('header-menu-mediathek');
        // Remove from favorites
        $this->iClickOnFirstElementWithTestId('tile__favor-button-video1');
    }

    /**
     * @Then I no longer see this video in "Meine Videofavoriten"
     */
    public function iNoLongerSeeThisVideoIn(): void
    {
        $this->iNavigateToMeineVideofavoritenOnTheSchreibtisch();

        $this->pageContainsTexts([
            'video2',
        ]);

        $this->pageNotContainTexts([
            'video1',
        ]);
    }

    private function createDummySolution(
        ExercisePhaseTeam $exercisePhaseTeam,
        string $id,
        string $content,
    ): void
    {
        $exercisePhase = $exercisePhaseTeam->getExercisePhase();

        // create solution
        $solution = new Solution($exercisePhaseTeam, $id, $content);

        // add solution to team
        $exercisePhaseTeam->setSolution($solution);

        $this->entityManager->persist($solution);
        $this->entityManager->persist($exercisePhaseTeam);
        $this->entityManager->flush();
    }

    /**
     * @Given I have a "Material" :materialId
     */
    public function iHaveAMaterial($materialId): void
    {
        $user = $this->currentUser;
        $testDozent = $this->createUser(self::TEST_DOZENT);

        // course
        $this->ensureCourseExists(self::TEST_COURSE_1);
        // courseRole
        $this->userHasCourseRole($user, CourseRole::STUDENT, self::TEST_COURSE_1);

        // exercise
        $this->createExerciseInCourseByUser(self::TEST_EXERCISE_1, self::TEST_COURSE_1, $testDozent);

        // material phase
        $materialPhase = $this->createPhaseOfTypeInExercise(
            $materialId,
            self::TEST_EXERCISE_1,
            ExercisePhaseType::MATERIAL
        );

        // WHY: we have to set logged in user to dozent to create exercises
        // so we need to set it to the previously logged in user afterwards
        $this->currentUser = $user;

        $exercisePhaseTeam = $this->ensureAnExerciseTeamForUserAndPhaseExists($user, $materialPhase);

        $this->createDummySolution($exercisePhaseTeam, 'solution1', '<p>Material</p>');

        $this->materialService->createMaterialForUser($user, $exercisePhaseTeam, $materialId);
    }

    /**
     * @When I access "Meine Materialien"
     */
    public function iAccessMeineMaterialien(): void
    {
        $this->queryResult = $this->materialService->getMaterialsForUser($this->currentUser);
    }

    /**
     * @Then My Material is shown
     */
    public function myMaterialIsShown(): void
    {
        /** @var Material $result */
        $result = array_map(fn ($material) => $material->getId(), $this->queryResult)[0];
        $expected = 'material1';

        assertEquals($expected, $result);
    }

    /**
     * @When I access :materialId from "Meine Materialien"
     */
    public function iAccessAMaterialFrom($materialId): void
    {
        $this->iNavigateToSchreibtisch();
        $this->iClickOn('Meine Materialien');

        $originalExercisePhaseId = $materialId;
        $originalExercisePhase = $this->exercisePhaseRepository->find($originalExercisePhaseId);
        $this->iClickOn($originalExercisePhase->getName());
    }

    /**
     * @When I edit material :materialId and change it to :materialValue
     */
    public function editThisMaterial($materialId, $materialValue): void
    {
        // edit material
        $this->playwrightConnector->execute(
            $this->playwrightContext,
            // language=JavaScript
            "
                await vars.page.fill('.ck-content', '$materialValue')
                await Promise.all([
                    vars.page.click('text=Speichern'),
                    vars.page.waitForRequest('**/schreibtisch/material/update/$materialId')
                ])
            "
        );
    }

    /**
     * @Then The material content should be :materialValue after a page reload
     */
    public function materialShouldBe($materialValue): void
    {
        $this->playwrightConnector->execute(
            $this->playwrightContext,
            // language=JavaScript
            "
                await vars.page.reload()
                await vars.page.waitForSelector('text=$materialValue')
            "
        );
    }

    /**
     * @Then The original solution of phase :phaseId remains untouched
     */
    public function theOriginalSolutionRemainsUntouched($phaseId): void
    {
        // get team's solution
        $exercisePhase = $this->exercisePhaseRepository->find($phaseId);

        $exercisePhaseTeam = $this->exercisePhaseTeamRepository->findByMemberAndExercisePhase($this->currentUser, $exercisePhase);
        $actual = $exercisePhaseTeam->getSolution()->getSolution()->getMaterial()->toString();

        $expected = '<p>Material</p>';

        assertEquals($expected, $actual);
    }

    /**
     * @Then A copy of the material from :phaseId should be added to the "Schreibtisch" of each user who was part of the Group which created the solution
     */
    public function aCopyOfTheMaterialShouldBeAddedToTheOfEachUserWhoWasPartOfTheGroupWhichCreatedTheSolution($phaseId): void
    {
        $student = $this->userRepository->find(self::TEST_STUDENT);
        $exercisePhase = $this->exercisePhaseRepository->find($phaseId);
        $exercisePhaseTeam = $this->exercisePhaseTeamRepository->findByMemberAndExercisePhase($student, $exercisePhase);

        $materials = $this->materialService->getMaterialsForUser($student);
        $materialForExercise = array_filter($materials, function (Material $material) use ($exercisePhaseTeam) {
            return $material->getOriginalPhaseTeam() === $exercisePhaseTeam;
        });

        // We just assert, that we receive the correct amount of materials after filtering (which is one, because a single user
        // can always only have a single material per exercisePhase).
        // Asserting the materialID would not work, because its set randomly and it would not make sense to dilute the
        // API of our finishPhase() method, just to be able to inject a custom ID in tests.
        assertCount(1, $materialForExercise);
    }

    /**
     * @When the material phase :phaseId is "IN_REVIEW"
     */
    public function theMaterialPhaseIs($phaseId): void
    {
        $materialPhase = $this->exercisePhaseRepository->find($phaseId);
        $phaseTeam = $this->exercisePhaseTeamRepository->findOneBy(["exercisePhase" => $materialPhase]);

        $phaseTeam->setStatus(ExercisePhaseStatus::IN_REVIEW);
        $this->entityManager->persist($phaseTeam);
        $this->entityManager->flush();
    }

    /**
     * @When Save this material
     */
    public function saveThisMaterial(): void
    {
        // edit material
        $this->playwrightConnector->execute(
            $this->playwrightContext,
            // language=JavaScript
            "
                await vars.page.click('text=Speichern')
            "
        );
    }

    /**
     * @Given material phase :phaseId requires a review: :reviewRequired
     */
    public function materialPhaseRequiresAReview($phaseId, $reviewRequired): void
    {
        $exercisePhase = $this->exercisePhaseRepository->find($phaseId);

        if ($exercisePhase instanceof MaterialPhase) {
            $reviewRequired = $reviewRequired === 'yes';
            $exercisePhase->setReviewRequired($reviewRequired);

            $this->entityManager->persist($exercisePhase);
            $this->entityManager->flush();
        }

        $user = $this->createUser(self::TEST_STUDENT, null, false, true);
        $course = $exercisePhase->getBelongsToExercise()->getCourse();
        $this->userHasCourseRole(self::TEST_STUDENT, CourseRole::STUDENT, $course->getId());

        $exercisePhaseTeam = $this->ensureAnExerciseTeamForUserAndPhaseExists($user, $exercisePhase);

        $this->createDummySolution($exercisePhaseTeam, 'solution1', '<p>Material</p>');
    }

    private function ensureFachbereichExists(string $fachbereichId): Fachbereich
    {
        /** @var Fachbereich $fachbereich */
        $fachbereich = $this->entityManager->find(Fachbereich::class, $fachbereichId);

        if (!$fachbereich) {
            $fachbereich = new Fachbereich($fachbereichId);
            $fachbereich->setName($fachbereichId);

            $this->entityManager->persist($fachbereich);
            $this->entityManager->flush();
        }

        return $fachbereich;
    }
}
