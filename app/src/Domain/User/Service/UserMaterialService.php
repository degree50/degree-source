<?php

namespace App\Domain\User\Service;

use App\Domain\ExercisePhaseTeam\Model\ExercisePhaseTeam;
use App\Domain\Material\Model\Material;
use App\Domain\Material\Repository\MaterialRepository;
use App\Domain\User\Model\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

readonly class UserMaterialService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MaterialRepository     $materialRepository,
    )
    {
    }

    /**
     *
     * @param User $user
     * @return Material[]
     */
    public function getMaterialsForUser(User $user): array
    {
        return $this->materialRepository->findByOwner($user);
    }

    public function updateMaterial(Material $material, string $newValue): void
    {
        $material->setMaterial($newValue);
        $material->setLastUpdatedAt(new DateTimeImmutable());

        $this->entityManager->persist($material);
        $this->entityManager->flush();
    }

    /**
     * Create a unique material for each member of an exercisePhaseTeam which is derived
     * from their final submitted solution.
     * The material can then be accessed and edited by each individual user on their "Schreibtisch"
     **/
    public function createMaterialsForExercisePhaseTeamMembers(ExercisePhaseTeam $exercisePhaseTeam): void
    {
        $members = $exercisePhaseTeam->getMembers();

        foreach ($members as $member) {
            $this->createMaterialForUser($member, $exercisePhaseTeam);
        }
    }

    /**
     * Create new Material entity from team solution.
     *
     * @param User $user
     * @param ExercisePhaseTeam $exercisePhaseTeam
     * @param string|null $id
     * @return void
     */
    public function createMaterialForUser(User $user, ExercisePhaseTeam $exercisePhaseTeam, string $id = null): void
    {
        $newMaterial = new Material($id);
        $newMaterial->setOwner($user);
        $newMaterial->setOriginalPhaseTeam($exercisePhaseTeam);
        $newMaterial->setCreatedAt(new DateTimeImmutable());
        $solution = $exercisePhaseTeam->getSolution();

        if (is_null($solution)) {
            // NoOp
            return;
        }

        $newMaterial->setMaterial($solution->getSolution()->getMaterial()->toString());

        $this->entityManager->persist($newMaterial);
        $this->entityManager->flush();
    }

    public function deleteMaterialsOfUser(User $user): void
    {
        $this->materialRepository->removeAllMaterialsOfUser($user);
    }
}
