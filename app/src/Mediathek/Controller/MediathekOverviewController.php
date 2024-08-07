<?php

namespace App\Mediathek\Controller;

use App\Domain\Course\Model\Course;
use App\Domain\Course\Repository\CourseRepository;
use App\Domain\User\Model\User;
use App\Domain\Video\Model\Video;
use App\Domain\Video\Repository\VideoRepository;
use App\Domain\VideoFavorite\Service\VideoFavouritesService;
use App\Mediathek\Dto\VideoWithFavoriteStatusDto;
use App\Security\Voter\DataPrivacyVoter;
use App\Security\Voter\TermsOfUseVoter;
use App\Security\Voter\UserVerifiedVoter;
use App\Security\Voter\VideoVoter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
#[IsGranted(UserVerifiedVoter::USER_VERIFIED)]
#[IsGranted(DataPrivacyVoter::ACCEPTED)]
#[IsGranted(TermsOfUseVoter::ACCEPTED)]
class MediathekOverviewController extends AbstractController
{
    public function __construct(
        private readonly VideoRepository        $videoRepository,
        private readonly CourseRepository       $courseRepository,
        private readonly VideoFavouritesService $videoFavouritesService,
        private readonly Security               $security,
    )
    {
    }

    #[Route("/mediathek/{id?}", name: "mediathek--index")]
    public function index(Course $course = null): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if ($course) {
            $videos = $this->videoRepository->findAllForCourse($course);
        } else {
            $videos = $this->videoRepository->findAllForUser($user);
        }

        return $this->render('Mediathek/Index.html.twig', [
            'sidebarItems' => $this->getSideBarItems(),
            'course' => $course,
            'groupedVideos' => $this->groupSelfCreatedAndOtherVideos($user, $videos),
        ]);
    }

    #[IsGranted(VideoVoter::FAVOR, subject: "video")]
    #[Route("/mediathek/favor/{id?}", name: "mediathek__video--favor")]
    public function toggleFavorVideo(Video $video = null): Response
    {
        if (!$video) {
            return $this->render("Security/403.html.twig");
        }

        /** @var User $user */
        $user = $this->security->getUser();

        $this->videoFavouritesService->toggleFavorite($video, $user);

        return $this->redirectToRoute('mediathek--index');
    }

    /**
     * @param Video[] $videos
     * @return array[]
     */
    private function groupSelfCreatedAndOtherVideos(User $user, array $videos): array
    {
        $videoCollection = new ArrayCollection($videos);

        $videosWithFavoriteStatus = $videoCollection->map(
            fn(Video $video) => new VideoWithFavoriteStatusDto(
                $video,
                $this->videoFavouritesService->videoIsFavorite($video, $user),
            )
        );

        $partitionedVideos = $videosWithFavoriteStatus->partition(
            fn($_key, VideoWithFavoriteStatusDto $videoDto) => $videoDto->video->getCreator() === $user
        );

        // this is the data structure used by the template
        return [
            [
                'id' => 'ownVideos',
                'videos' => $partitionedVideos[0]->toArray(),
            ],
            [
                'id' => 'otherVideos',
                'videos' => $partitionedVideos[1]->toArray(),
            ]
        ];
    }

    /**
     * @return array
     */
    private function getSideBarItems(): array
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $criteria = Criteria::create()->orderBy(['name' => 'ASC']);

        $courses = $this->courseRepository->findAllForUserWithCriteria($user, $criteria);

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
