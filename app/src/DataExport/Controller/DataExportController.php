<?php

namespace App\DataExport\Controller;

use App\DataExport\Service\DegreeDataToCsvService;
use App\Domain\Course\Model\Course;
use App\Security\Voter\CourseVoter;
use App\Security\Voter\DataPrivacyVoter;
use App\Security\Voter\TermsOfUseVoter;
use App\Security\Voter\UserVerifiedVoter;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use ZipArchive;

#[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
#[IsGranted(UserVerifiedVoter::USER_VERIFIED)]
#[IsGranted(DataPrivacyVoter::ACCEPTED)]
#[IsGranted(TermsOfUseVoter::ACCEPTED)]
class DataExportController extends AbstractController
{
    public function __construct(
        private readonly DegreeDataToCsvService $degreeDataToCsvService,
    )
    {
    }

    #[IsGranted(CourseVoter::EXPORT_CSV, subject: "course")]
    #[Route("/exercise-overview/{id}/export-csv", name: "exercise-overview__course-export-csv")]
    public function exportData(Course $course = null): Response
    {
        if (!$course) {
            return new Response('not allowed', '403');
        }

        $csvList = $this->degreeDataToCsvService->getAllAsVirtualCSVs($course);

        // Create temporary zip file
        $zipName = 'course_' . $course->getId() . '_csv-export.zip';
        $zipPath = sys_get_temp_dir() . '/' . Uuid::uuid4() . '_' . $zipName;

        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE);
        foreach ($csvList as $csvDto) {
            $zip->addFromString($csvDto->getFileName(), $csvDto->getContentString());
        }
        $zip->close();

        $response = new Response(file_get_contents($zipPath));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $zipName . '"');
        $response->headers->set('Content-length', filesize($zipPath));

        // Remove zip file from file system
        unlink($zipPath);

        return $response;
    }
}
