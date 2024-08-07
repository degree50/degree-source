<?php

namespace App\Schreibtisch\Controller;

use App\Domain\Material\Model\Material;
use App\Domain\User\Model\User;
use App\Domain\User\Service\UserMaterialService;
use App\Domain\Video\Model\Video;
use App\Domain\VideoFavorite\Service\VideoFavouritesService;
use App\Schreibtisch\Service\SchreibtischService;
use App\Security\Voter\DataPrivacyVoter;
use App\Security\Voter\TermsOfUseVoter;
use App\Security\Voter\UserVerifiedVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
#[IsGranted(UserVerifiedVoter::USER_VERIFIED)]
#[IsGranted(DataPrivacyVoter::ACCEPTED)]
#[IsGranted(TermsOfUseVoter::ACCEPTED)]
class SchreibtischController extends AbstractController
{

    public function __construct(
        private readonly SchreibtischService    $schreibtischService,
        private readonly VideoFavouritesService $videoFavouritesService,
        private readonly UserMaterialService    $materialService,
    )
    {
    }

    #[Route("/schreibtisch", name: "schreibtisch")]
    public function index(): Response
    {
        return $this->render('Schreibtisch/Index.html.twig');
    }

    #[Route("/schreibtisch/exercises", name: "schreibtisch-exercises-api")]
    public function getExercises(): Response
    {
        $responseData = json_encode($this->schreibtischService->getExercisesApiResponse());

        return new Response($responseData, 200);
    }

    #[Route("/schreibtisch/material", name: "schreibtisch-material-api")]
    public function getMaterial(): Response
    {
        $responseData = json_encode($this->schreibtischService->getMaterialResponse());

        return new Response($responseData, 200);
    }

    #[Route("/schreibtisch/material/update/{id}", name: "schreibtisch-material-update")]
    public function updateMaterial(Request $request, Material $material = null): Response
    {
        if (!$material) {
            return new Response('not allowed', 403);
        }

        if ($content = $request->getContent()) {
            $updatedContent = json_decode($content, true);
            $this->materialService->updateMaterial($material, $updatedContent['material']);
        }

        return new Response();
    }

    #[Route("/schreibtisch/video-favorites", name: "schreibtisch-video-favorites-api")]
    public function getVideoFavorites(): Response
    {
        $responseData = json_encode($this->schreibtischService->getVideoFavoritesResponse());

        return new Response($responseData, 200);
    }

    #[Route("/schreibtisch/video-favorites/toggle/{id}", name: "schreibtisch-video-favorite-toggle", methods: ["POST"])]
    public function toggleVideoFavorite(Video $video = null): Response
    {
        if (!$video) {
            return new Response('not allowed', 403);
        }

        /** @var User $user */
        $user = $this->getUser();
        $this->videoFavouritesService->toggleFavorite($video, $user);

        return new Response();
    }

    #[Route("/schreibtisch/fachbereiche", name: "schreibtisch-fachbereiche-api")]
    public function getFachbereiche(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $responseData = json_encode($this->schreibtischService->getFachbereicheResponse($user));

        return new Response($responseData, 200);
    }

    #[Route("/schreibtisch/courses", name: "schreibtisch-courses-api")]
    public function getCourses(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $responseData = json_encode($this->schreibtischService->getCoursesResponse($user));

        return new Response($responseData, 200);
    }
}
