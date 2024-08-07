<?php

namespace App\Domain\Solution\Dto\ClientSideSolutionData;

use App\Domain\ExercisePhase\Model\ExercisePhaseStatus;
use App\Domain\ExercisePhase\Service\ExercisePhaseService;
use App\Domain\ExercisePhaseTeam\Model\ExercisePhaseTeam;
use App\Domain\Solution\Dto\ServerSideSolutionData\ServerSideAnnotation;
use App\Domain\Solution\Dto\ServerSideSolutionData\ServerSideCut;
use App\Domain\Solution\Dto\ServerSideSolutionData\ServerSideMaterial;
use App\Domain\Solution\Dto\ServerSideSolutionData\ServerSideSolutionData;
use App\Domain\Solution\Dto\ServerSideSolutionData\ServerSideVideoCode;
use App\Domain\User\Model\User;
use JsonSerializable;

/**
 * This builder allows to transform server side solution value objects into a normalized
 * data structure which can be serialized and consumed by the frontend.
 *
 * WHY:
 * Our solution model only stores solutionData as jsonBlob. This is totally fine
 * for the backend side of things. However our frontend treats annotations, videoCodes etc.
 * as entities and therefore wants them to have ids.
 * This builder serves as a single source of truth of the conversion from serverside solution
 * data to normalized client side solution data.
 *
 * NOTE: The ids for annotations, videoCodes etc. are usually created by concatenating the id
 * of the parent solution with the index of the item inside its parent list.
 * For example
 * @see ClientSideAnnotation::fromServerSideAnnotation()
 *
 * An additional benefit is the enhanced type safety we gain from using value objects for client/server side
 * representations of solution objects.
 */
class ClientSideSolutionDataBuilder implements JsonSerializable
{
    /**
     * @param ExercisePhaseService $exercisePhaseService
     * @param ClientSideSolution[] $clientSideSolutions
     * @param ClientSideAnnotation[] $clientSideAnnotations
     * @param ClientSideVideoCode[] $clientSideVideoCodes
     * @param ClientSideCut[] $clientSideCuts
     * @param ClientSideVideoCodePrototype[] $clientSideVideoCodePrototypes
     * @param string|null $currentSolutionId
     * @param string[] $previousSolutionIds
     * @param ClientSideMaterial[] $materials
     */
    public function __construct(
        private readonly ExercisePhaseService $exercisePhaseService,
        private array                         $clientSideSolutions = [],
        private array                         $clientSideAnnotations = [],
        private array                         $clientSideVideoCodes = [],
        private array                         $clientSideCuts = [],
        private array                         $clientSideVideoCodePrototypes = [],
        private ?string                       $currentSolutionId = null,
        private array                         $previousSolutionIds = [],
        private array                         $materials = [],
    )
    {
    }

    public function jsonSerialize(): array
    {
        return [
            'solutions' => [
                'byId' => $this->clientSideSolutions,
                'current' => $this->currentSolutionId,
                'previous' => $this->previousSolutionIds,
            ],
            'annotations' => [
                'byId' => $this->clientSideAnnotations,
            ],
            'videoCodes' => [
                'byId' => $this->clientSideVideoCodes,
            ],
            'cuts' => [
                'byId' => $this->clientSideCuts,
            ],
            'videoCodePrototypes' => [
                'byId' => $this->clientSideVideoCodePrototypes,
            ],
            'materials' => [
                'byId' => $this->materials,
            ]
        ];
    }

    public function addCurrentSolution(
        ServerSideSolutionData $serverSideSolutionData,
        ExercisePhaseTeam      $exercisePhaseTeam,
        ?ClientSideCutVideo    $cutVideo
    ): static
    {
        $solutionId = $exercisePhaseTeam->getSolution()->getId();
        $exercisePhase = $exercisePhaseTeam->getExercisePhase();

        $this->addSolution(
            $serverSideSolutionData,
            $solutionId,
            $exercisePhaseTeam->getCreator(),
            $cutVideo,
            $exercisePhase->isGroupPhase(),
            $this->exercisePhaseService->getStatusForTeam($exercisePhaseTeam)
        );

        $this->currentSolutionId = $solutionId;

        return $this;
    }

    public function addPreviousSolution(
        ServerSideSolutionData $serverSideSolutionData,
        string                 $solutionId,
        User                   $teamMember,
        ?ClientSideCutVideo    $cutVideo,
        ?bool                  $fromGroupPhase,
        ?ExercisePhaseStatus   $status,
    ): static
    {
        $solutionId = $this->addSolution(
            $serverSideSolutionData,
            $solutionId,
            $teamMember,
            $cutVideo,
            $fromGroupPhase,
            $status,
        );

        $this->previousSolutionIds[] = $solutionId;

        return $this;
    }

    public function addVideoCodePrototypesToSolution(array $serverSideVideoCodePrototypes, string $solutionId): static
    {
        $solution = $this->clientSideSolutions[$solutionId];

        $this->clientSideSolutions[$solutionId] = ClientSideSolution::create(
            ClientSideSolutionData::create(
                $solution->getClientSideSolutionData()->getAnnotationIds(),
                $solution->getClientSideSolutionData()->getVideoCodeIds(),
                $solution->getClientSideSolutionData()->getCutIds(),
                array_unique(array_merge(
                    $solution->getClientSideSolutionData()->getVideoCodePrototypeIds(),
                    $this->_addVideoCodePrototypes($serverSideVideoCodePrototypes)
                )),
                $solution->getClientSideSolutionData()->getMaterialId(),
            ),
            $solutionId,
            $solution->getUserName(),
            $solution->getUserId(),
            $solution->getCutVideo(),
            $solution->getFromGroupPhase(),
            $solution->getStatus()
        );

        return $this;
    }

    /**
     * Creates clientSidePrototypes from serverSidePrototypes and adds them to the clientSideVideoCodePrototypesList
     * Returns the builder itself, so that further methods can be chained.
     */
    public function addVideoCodePrototypes(array $serverSideVideoCodePrototypes): static
    {
        $this->_addVideoCodePrototypes($serverSideVideoCodePrototypes);
        return $this;
    }

    private function addSolution(
        ServerSideSolutionData $serverSideSolutionData,
        string                 $solutionId,
        User                   $solutionCreator,
        ?ClientSideCutVideo    $cutVideo,
        ?bool                  $fromGroupPhase,
        ExercisePhaseStatus    $status,
    ): string
    {
        $userName = $solutionCreator->getUsername();
        $userId = $solutionCreator->getId();

        $serverSideMaterial = $serverSideSolutionData->getMaterial();
        $materialId = $this->addMaterial($serverSideMaterial, $solutionId);

        $serverSideAnnotations = $serverSideSolutionData->getAnnotations();
        $clientSideAnnotationIds = $this->addAnnotations($serverSideAnnotations, $solutionId);

        $serverSideVideoCodes = $serverSideSolutionData->getVideoCodes();
        $clientSideVideoCodeIds = $this->addVideoCodes($serverSideVideoCodes, $solutionId);

        $serverSideCutList = $serverSideSolutionData->getCutList();
        $clientSideCutIds = $this->addCuts($serverSideCutList, $solutionId);

        $serverSideVideoCodePrototypes = $serverSideSolutionData->getVideoCodePrototypes();
        $clientSideVideoCodePrototypeIds = $this->_addVideoCodePrototypes($serverSideVideoCodePrototypes);

        $this->clientSideSolutions[$solutionId] = ClientSideSolution::create(
            ClientSideSolutionData::create(
                $clientSideAnnotationIds,
                $clientSideVideoCodeIds,
                $clientSideCutIds,
                $clientSideVideoCodePrototypeIds,
                $materialId,
            ),
            $solutionId,
            $userName,
            $userId,
            $cutVideo,
            $fromGroupPhase,
            $status,
        );

        return $solutionId;
    }

    /**
     * @param ServerSideMaterial|null $serverSideMaterial
     * @param string $solutionId
     * @return string|null Material Id if it exists.
     */
    private function addMaterial(?ServerSideMaterial $serverSideMaterial, string $solutionId): string|null
    {
        if (!empty($serverSideMaterial)) {
            $clientSideMaterial = ClientSideMaterial::fromServerSideMaterial($serverSideMaterial, $solutionId);
            $this->materials[$clientSideMaterial->getId()] = $clientSideMaterial;
            return $clientSideMaterial->getId();
        } else {
            return null;
        }
    }

    private function addAnnotations(array $serverSideAnnotations, string $solutionId): array
    {
        $clientSideAnnotations = array_map(function (ServerSideAnnotation $annotation, int $index) use ($solutionId) {
            return ClientSideAnnotation::fromServerSideAnnotation($annotation, $solutionId, $index);
        }, $serverSideAnnotations, array_keys($serverSideAnnotations));

        foreach ($clientSideAnnotations as $clientSideAnnotation) {
            $this->clientSideAnnotations[$clientSideAnnotation->getId()] = $clientSideAnnotation;
        }

        $clientSideAnnotationIds = array_map(function (ClientSideAnnotation $annotation) {
            return $annotation->getId();
        }, $clientSideAnnotations);

        return $clientSideAnnotationIds;
    }

    private function addVideoCodes(array $serverSideVideoCodes, string $solutionId): array
    {
        $clientSideVideoCodes = array_map(function (ServerSideVideoCode $videoCode, int $index) use ($solutionId) {
            return ClientSideVideoCode::fromServerSideVideoCode($videoCode, $solutionId, $index);
        }, $serverSideVideoCodes, array_keys($serverSideVideoCodes));

        foreach ($clientSideVideoCodes as $clientSideVideoCode) {
            $this->clientSideVideoCodes[$clientSideVideoCode->getId()] = $clientSideVideoCode;
        }

        $clientSideVideoCodeIds = array_map(function (ClientSidevideoCode $videoCode) {
            return $videoCode->getId();
        }, $clientSideVideoCodes);

        return $clientSideVideoCodeIds;
    }

    private function addCuts(array $serverSideCutList, string $solutionId): array
    {
        $clientSideCutList = array_map(function (ServerSideCut $cut, int $index) use ($solutionId) {
            return ClientSideCut::fromServerSideCut($cut, $solutionId, $index);
        }, $serverSideCutList, array_keys($serverSideCutList));

        foreach ($clientSideCutList as $clientSideCut) {
            $this->clientSideCuts[$clientSideCut->getId()] = $clientSideCut;
        }

        $clientSideCutIds = array_map(function (ClientSideCut $cut) {
            return $cut->getId();
        }, $clientSideCutList);

        return $clientSideCutIds;
    }

    /**
     * Creates clientSidePrototypes from serverSidePrototypes and adds them to the clientSideVideoCodePrototypesList
     * Returns the resulting list.
     *
     * For internal use only!
     */
    private function _addVideoCodePrototypes(array $serverSideVideoCodePrototypes): array
    {
        $clientSideVideoCodePrototypes = array_reduce($serverSideVideoCodePrototypes, function ($carry, $videoCodePrototype) {
            $parentPrototype = ClientSideVideoCodePrototype::fromServerSideVideoCodePrototype($videoCodePrototype);
            $childPrototypes = array_map(function ($childPrototype) {
                return ClientSideVideoCodePrototype::fromServerSideVideoCodePrototype($childPrototype);
            }, $videoCodePrototype->getChildServerSidePrototypes());

            return array_merge($carry, [$parentPrototype], $childPrototypes);
        }, []);

        foreach ($clientSideVideoCodePrototypes as $clientSideVideoCodePrototype) {
            $this->clientSideVideoCodePrototypes[$clientSideVideoCodePrototype->getId()] = $clientSideVideoCodePrototype;
        }

        $clientSideVideoCodePrototypeIds = array_map(function (ClientSidevideoCodePrototype $videoCodePrototype) {
            return $videoCodePrototype->getId();
        }, $clientSideVideoCodePrototypes);

        return $clientSideVideoCodePrototypeIds;
    }
}
