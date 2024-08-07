<?php

namespace App\Domain\Exercise\Controller;

use App\Domain\Course\Model\Course;
use App\Domain\Course\Repository\CourseRepository;
use App\Domain\Exercise\Dto\GroupedExercisesBuilder;
use App\Domain\Exercise\Model\Exercise;
use App\Domain\Exercise\Repository\ExerciseRepository;
use App\Domain\Exercise\Service\ExerciseService;
use App\Domain\User\Model\User;
use App\Security\Voter\DataPrivacyVoter;
use App\Security\Voter\TermsOfUseVoter;
use App\Security\Voter\UserVerifiedVoter;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * This controller is responsible for actions regarding the exercise overview
 *
 * Exercises are being displayed as tiles on the templates.
 * NOTE: This has nothing to do with the overview of a single exercise.
 * Single exercise overview handling is located inside the [ExerciseController]
 */
#[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
#[IsGranted(UserVerifiedVoter::USER_VERIFIED)]
#[IsGranted(DataPrivacyVoter::ACCEPTED)]
#[IsGranted(TermsOfUseVoter::ACCEPTED)]
class ExerciseOverviewController extends AbstractController
{
    /**
     * @param CourseRepository $courseRepository
     * @param ExerciseRepository $exerciseRepository
     */
    public function __construct(
        private readonly CourseRepository   $courseRepository,
        private readonly ExerciseRepository $exerciseRepository,
        private readonly ExerciseService    $exerciseService,
    )
    {
    }

    #[Route("/", name: "app")]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user->isStudent()) {
            return $this->redirectToRoute('schreibtisch');
        } else {
            return $this->redirectToRoute('exercise-overview');
        }
    }

    #[Route("/exercise-overview/{id?}", name: "exercise-overview")]
    public function overview(Request $request, ?string $id): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        /* @var Course|null $course */
        $course = null;

        $statusFilter = $request->query->get('status');

        $queryCriteria = Criteria::create();
        if ($id != null) {
            $course = $this->courseRepository->findOneForUserWithCriteria(
                $user,
                Criteria::create()->andWhere(Criteria::expr()->eq('id', $id))
            );

            if ($course == null) {
                return $this->render('Security/403.html.twig')->setStatusCode(Response::HTTP_FORBIDDEN);
            }

            $queryCriteria->andWhere(Criteria::expr()->eq('course', $course));
        }

        if ($statusFilter != null) {
            $queryCriteria->andWhere(Criteria::expr()->eq('status', intval($statusFilter)));
        }

        $queryCriteria->orderBy(['createdAt' => 'DESC']);

        $groupedExercises = $this->getExercisesGrouped($this->exerciseRepository->findAllForUserWithCriteria($user, $queryCriteria));

        return $this->render('ExerciseOverview/Index.html.twig', [
            'sidebarItems' => $this->getSideBarItems(),
            'course' => $course,
            'courseId' => $course?->getId(),
            'activeFilter' => $statusFilter,
            'groupedExercises' => $groupedExercises,
        ]);
    }

    private function getExercisesGrouped(array $exercises): array
    {
        /** @var User $user */
        $user = $this->getUser();

        $groupedExercisesBuilder = new GroupedExercisesBuilder($this->exerciseService);

        foreach ($exercises as $_key => $exercise) {
            /** @var Exercise $exercise */
            if ($exercise->getCreator() === $user) {
                $groupedExercisesBuilder->addOwnExercise($exercise);
            } else {
                $groupedExercisesBuilder->addOtherExercise($exercise);
            }
        }

        return $groupedExercisesBuilder->create();
    }

    /**
     * @return array
     */
    private function getSideBarItems(): array
    {
        /* @var User $user */
        $user = $this->getUser();

        $courses = $this->courseRepository->findAllForUserWithCriteria($user);

        $sidebarItems = [];
        foreach ($courses as $course) {
            $creationDateYear = $course->getCreationDateYear();
            if (!array_key_exists($creationDateYear, $sidebarItems)) {
                $sidebarItems[$creationDateYear] = ['label' => $creationDateYear, 'courses' => []];
            }
            $sidebarItems[$creationDateYear]['courses'][] = $course;
        }

        // WHY: We want to sort the years DESC in the sidebar
        // ! mutation !
        krsort($sidebarItems, SORT_NUMERIC);

        return $sidebarItems;
    }
}
