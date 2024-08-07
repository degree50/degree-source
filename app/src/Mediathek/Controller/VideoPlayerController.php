<?php

namespace App\Mediathek\Controller;

use App\Domain\Video\Model\Video;
use App\Security\Voter\DataPrivacyVoter;
use App\Security\Voter\TermsOfUseVoter;
use App\Security\Voter\UserVerifiedVoter;
use App\Security\Voter\VideoVoter;
use App\Twig\AppRuntime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
#[IsGranted(UserVerifiedVoter::USER_VERIFIED)]
#[IsGranted(DataPrivacyVoter::ACCEPTED)]
#[IsGranted(TermsOfUseVoter::ACCEPTED)]
class VideoPlayerController extends AbstractController
{
    public function __construct(private readonly AppRuntime $appRuntime)
    {
    }

    #[IsGranted(VideoVoter::VIEW, subject: "video")]
    #[Route("/video/play/{id}", name: "mediathek__video--player")]
    public function videoPlayer(
        Video $video = null
    ): Response
    {
        if (!$video) {
            return $this->render("Security/403.html.twig");
        }

        return $this->render('Mediathek/VideoUpload/VideoPlayer.html.twig', [
            'video' => $video,
            'videoMap' => $video->getAsClientSideVideo($this->appRuntime)
        ]);
    }
}
