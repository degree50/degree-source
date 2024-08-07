<?php

namespace App\Legal\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LegalController extends AbstractController
{
    #[Route("/impressum", name: "imprint")]
    public function imprint(): Response
    {
        return $this->render('Standard/Imprint.html.twig');
    }

    #[Route("/datenschutz", name: "data-privacy")]
    public function dataPrivacy(): Response
    {
        return $this->render('Standard/DataPrivacy.html.twig');
    }

    #[Route("/nutzungsbedingungen", name: "terms-of-use")]
    public function termsOfUse(): Response
    {
        return $this->render('Standard/TermsOfUse.html.twig');
    }
}
