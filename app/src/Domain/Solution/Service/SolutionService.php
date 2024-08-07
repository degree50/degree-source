<?php

namespace App\Domain\Solution\Service;

use App\Domain\AutosavedSolution\Repository\AutosavedSolutionRepository;
use App\Domain\CutVideo\Service\CutVideoService;
use App\Domain\ExercisePhase\Model\ExercisePhase;
use App\Domain\ExercisePhase\Model\VideoAnalysisPhase;
use App\Domain\ExercisePhase\Service\ExercisePhaseService;
use App\Domain\ExercisePhaseTeam\Model\ExercisePhaseTeam;
use App\Domain\ExercisePhaseTeam\Repository\ExercisePhaseTeamRepository;
use App\Domain\Solution\Dto\ClientSideSolutionData\ClientSideSolutionDataBuilder;
use App\Domain\Solution\Dto\PreviousSolutionDto;
use App\Domain\Solution\Dto\ServerSideSolutionData\ServerSideVideoCodePrototype;
use App\Domain\User\Model\User;
use App\Domain\VideoCode\Model\VideoCode;
use App\Twig\AppRuntime;
use Doctrine\ORM\EntityNotFoundException;

readonly class SolutionService
{
    public function __construct(
        private AutosavedSolutionRepository $autosavedSolutionRepository,
        private AppRuntime                  $appRuntime,
        private ExercisePhaseTeamRepository $exercisePhaseTeamRepository,
        private ExercisePhaseService        $exercisePhaseService,
        private CutVideoService             $cutVideoService,
    )
    {
    }

    /**
     * @param ClientSideSolutionDataBuilder $clientSideSolutionDataBuilder
     * @param ExercisePhaseTeam[] $teams
     * @return ClientSideSolutionDataBuilder
     */
    public function retrieveAndAddDataToClientSideDataBuilderForSolutionView(
        ClientSideSolutionDataBuilder $clientSideSolutionDataBuilder,
        array                         $teams
    ): ClientSideSolutionDataBuilder
    {
        $previousSolutionDtos = array_map(function ($exercisePhaseTeam) {
            $solutionEntity = $exercisePhaseTeam->getSolution();
            $exercisePhase = $exercisePhaseTeam->getExercisePhase();

            $cutVideo = $this->cutVideoService->getCutVideoOfSolution($solutionEntity);
            $clientSideCutVideo = $cutVideo?->getAsClientSideVideo($this->appRuntime);

            return PreviousSolutionDto::create(
                $exercisePhaseTeam->getCreator(),
                $solutionEntity->getSolution(),
                $solutionEntity->getId(),
                $clientSideCutVideo,
                $exercisePhase->isGroupPhase(),
                $this->exercisePhaseService->getStatusForTeam($exercisePhaseTeam)
            );
        }, $teams);

        /**
         * @var PreviousSolutionDto $previousSolutionDto
         */
        foreach ($previousSolutionDtos as $previousSolutionDto) {
            $clientSideSolutionDataBuilder->addPreviousSolution(
                $previousSolutionDto->getServerSideSolutionData(),
                $previousSolutionDto->getSolutionId(),
                $previousSolutionDto->getTeamMember(),
                $previousSolutionDto->getCutVideo(),
                $previousSolutionDto->getFromGroupPhase(),
                $previousSolutionDto->getStatus(),
            );
        }

        // Get configured videoCodePrototypes from ExercisePhase
        if (!empty($teams)) {
            $exercisePhase = $teams[0]->getExercisePhase();

            $configuredVideoCodePrototypes = [];
            if ($exercisePhase instanceof VideoAnalysisPhase) {
                $configuredVideoCodePrototypes = array_map(function (VideoCode $videoCodePrototypeEntity) {
                    return ServerSideVideoCodePrototype::fromVideoCodeEntity($videoCodePrototypeEntity);
                }, $exercisePhase->getVideoCodes()->toArray());
            }

            $clientSideSolutionDataBuilder->addVideoCodePrototypes($configuredVideoCodePrototypes);
        }

        return $clientSideSolutionDataBuilder;
    }

    public function retrieveAndAddDataToClientSideDataBuilder(
        ClientSideSolutionDataBuilder $clientSideSolutionDataBuilder,
        ExercisePhaseTeam             $exercisePhaseTeam,
        ExercisePhase                 $exercisePhase
    ): ClientSideSolutionDataBuilder
    {
        // Note: This might either be an autosaved solution or an actual solution
        // FIXME: we should probably find a better way to handle solutions and autosavedSolutions in general.
        $solutionEntity = $this->autosavedSolutionRepository->getLatestSolutionOfExerciseTeam($exercisePhaseTeam);

        $configuredVideoCodePrototypes = [];

        if ($exercisePhase instanceof VideoAnalysisPhase) {
            $configuredVideoCodePrototypes = array_map(function (VideoCode $videoCodePrototypeEntity) {
                return ServerSideVideoCodePrototype::fromVideoCodeEntity($videoCodePrototypeEntity);
            }, $exercisePhase->getVideoCodes()->toArray());
        }

        $solutionId = $exercisePhaseTeam->getSolution()->getId();
        $previousSolutionDtos = $this->getPreviousSolutionDtosForVideoEditor($exercisePhase, $exercisePhaseTeam);

        $cutVideo = $this->cutVideoService->getCutVideoOfSolution($exercisePhaseTeam->getSolution());

        $clientSideCutVideo = $cutVideo?->getAsClientSideVideo($this->appRuntime);

        $clientSideSolutionDataBuilder
            ->addCurrentSolution($solutionEntity->getSolution(), $exercisePhaseTeam, $clientSideCutVideo)
            ->addVideoCodePrototypesToSolution($configuredVideoCodePrototypes, $solutionId);

        /**
         * @var PreviousSolutionDto $previousSolutionDto
         */
        foreach ($previousSolutionDtos as $previousSolutionDto) {
            $clientSideSolutionDataBuilder->addPreviousSolution(
                $previousSolutionDto->getServerSideSolutionData(),
                $previousSolutionDto->getSolutionId(),
                $previousSolutionDto->getTeamMember(),
                $previousSolutionDto->getCutVideo(),
                $previousSolutionDto->getFromGroupPhase(),
                $previousSolutionDto->getStatus(),
            );
        }

        return $clientSideSolutionDataBuilder;
    }

    private function getPreviousSolutionDtosForVideoEditor(
        ExercisePhase     $exercisePhase,
        ExercisePhaseTeam $exercisePhaseTeam = null
    )
    {
        // Get the relevant solutions of the previous phase,
        // meaning we get the solutions of each of the members of the current team
        $exercisePhaseDependedOn = $exercisePhase->getDependsOnExercisePhase();
        if ($exercisePhaseDependedOn !== null && $exercisePhaseTeam != null) {
            return array_reduce(
                $exercisePhaseTeam->getMembers()->toArray(),
                function (array $carry, User $teamMember) use ($exercisePhaseDependedOn, $exercisePhaseTeam) {
                    $teamOfPreviousPhase = $this->exercisePhaseTeamRepository->findByMemberAndExercisePhase($teamMember, $exercisePhaseDependedOn);
                    $solutionEntity = $teamOfPreviousPhase?->getSolution();

                    if (is_null($solutionEntity)) {
                        return $carry;
                    }

                    $clientSideCutVideo = null;

                    try {
                        $clientSideCutVideo = $this->cutVideoService->getCutVideoOfSolution($solutionEntity)?->getAsClientSideVideo($this->appRuntime);
                    } catch (EntityNotFoundException $e) {
                    }

                    return array_merge($carry, [PreviousSolutionDto::create(
                        $teamMember,
                        $solutionEntity->getSolution(),
                        $solutionEntity->getId(),
                        $clientSideCutVideo,
                        $exercisePhaseDependedOn->isGroupPhase(),
                        $this->exercisePhaseService->getStatusForTeam($exercisePhaseTeam)
                    )]);
                },
                []
            );
        }

        return [];
    }
}
