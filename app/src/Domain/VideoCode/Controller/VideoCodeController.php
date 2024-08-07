<?php

namespace App\Domain\VideoCode\Controller;

use App\Domain\ExercisePhase\Model\VideoAnalysisPhase;
use App\Domain\VideoCode\Model\VideoCode;
use App\Security\Voter\DataPrivacyVoter;
use App\Security\Voter\TermsOfUseVoter;
use App\Security\Voter\UserVerifiedVoter;
use Doctrine\ORM\EntityManagerInterface;
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
class VideoCodeController extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    /**
     * Used only asynchronous
     */
    #[Route("/video-codes/add/{id}", name: "video-code__add", methods: ["POST"])]
    public function add(Request $request, VideoAnalysisPhase $exercisePhase = null): Response
    {
        if (!$exercisePhase) {
            return new Response('not allowed', 403);
        }

        $color = json_decode($request->getContent(), true)['color'];
        $name = json_decode($request->getContent(), true)['name'];
        $videoCode = new VideoCode();
        $videoCode->setName($name);
        $videoCode->setColor($color);
        $videoCode->setExercisePhase($exercisePhase);

        $this->entityManager->persist($videoCode);
        $this->entityManager->flush();

        return new Response('OK');
    }

    /**
     * Used only asynchronous
     */
    #[Route("/video-codes/delete/{id}", name: "video-code__delete", methods: ["GET"])]
    public function delete(VideoCode $videoCode = null): Response
    {
        if (!$videoCode) {
            return new Response('not allowed', 403);
        }

        $this->entityManager->remove($videoCode);
        $this->entityManager->flush();

        return new Response('OK');
    }

    #[Route("/video-codes/list/{id}", name: "video-code__list")]
    public function videoCodes(VideoAnalysisPhase $exercisePhase = null): Response
    {
        if (!$exercisePhase) {
            return $this->render("Security/403.html.twig");
        }

        return $this->render('ExercisePhase/VideoCodesList.html.twig', [
            'videoCodes' => $exercisePhase->getVideoCodes()
        ]);
    }
}
