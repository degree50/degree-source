<?php

namespace App\Tests\Behat;

use App\Domain\AutosavedSolution\Model\AutosavedSolution;
use App\Domain\Exercise\Model\Exercise;
use App\Domain\ExercisePhase\Model\ExercisePhase;
use App\Domain\ExercisePhase\Model\ExercisePhaseType;
use App\Domain\ExercisePhase\Model\VideoAnalysisPhase;
use App\Domain\ExercisePhaseTeam\Model\ExercisePhaseTeam;
use App\Domain\Solution\Dto\ClientSideSolutionData\ClientSideSolutionDataBuilder;
use App\Domain\Solution\Dto\ServerSideSolutionData\ServerSideSolutionData;
use App\Domain\Solution\Model\Solution;
use App\Domain\User\Model\User;
use App\Domain\VideoCode\Model\VideoCode;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertEqualsCanonicalizing;
use function PHPUnit\Framework\assertJsonFileEqualsJsonFile;
use function PHPUnit\Framework\assertNotEquals;
use function PHPUnit\Framework\assertNotNull;

trait ExercisePhaseContextTrait
{
    /**
     * @Given I have an exercise phase :exercisePhaseId belonging to exercise :exerciseId
     */
    public function iHaveAnExercisePhaseBelongingToExercise($exercisePhaseId, $exerciseId): void
    {
        /** @var Exercise $exercise */
        $exercise = $this->entityManager->find(Exercise::class, $exerciseId);
        $exercisePhase = new VideoAnalysisPhase($exercisePhaseId);
        $exercisePhase->setName($exercisePhaseId);
        $exercise->addPhase($exercisePhase);

        $this->entityManager->persist($exercise);
        $this->entityManager->flush();
    }

    /**
     * @Given I have a team with ID :teamId belonging to exercise phase :exercisePhaseId
     */
    public function iHaveATeamWithIdBelongingToExercisePhase($teamId, $exercisePhaseId): void
    {
        /** @var ExercisePhase $exercisePhase */
        $exercisePhase = $this->entityManager->find(ExercisePhase::class, $exercisePhaseId);

        $exercisePhaseTeam = new ExercisePhaseTeam($teamId);
        $exercisePhaseTeam->setExercisePhase($exercisePhase);
        $exercisePhaseTeam->setCreator($this->currentUser);

        $exercisePhase->addTeam($exercisePhaseTeam);

        $this->entityManager->persist($exercisePhaseTeam);
        $this->entityManager->persist($exercisePhase);
        $this->entityManager->flush();
    }

    /**
     * @Given I have a team with ID :teamId belonging to exercise phase :exercisePhaseId and creator :creatorId
     */
    public function iHaveATeamWithIdBelongingToExercisePhaseAndCreatorId($teamId, $exercisePhaseId, $creatorId): void
    {
        $exercisePhase = $this->exercisePhaseRepository->find($exercisePhaseId);
        /** @var User $creator */
        $creator = $this->entityManager->find(User::class, $creatorId);

        $exercisePhaseTeam = new ExercisePhaseTeam($teamId);
        $exercisePhaseTeam->setCreator($creator);
        $exercisePhaseTeam->setExercisePhase($exercisePhase);

        $exercisePhase->addTeam($exercisePhaseTeam);

        $this->entityManager->persist($exercisePhaseTeam);
        $this->entityManager->persist($exercisePhase);
        $this->entityManager->flush();
    }

    /**
     * @Given I have a video :videoId belonging to exercise phase :exercisePhaseId
     */
    public function iHaveAVideoBelongingToExercisePhase($videoId, $exercisePhaseId): void
    {
        $exercisePhase = $this->exercisePhaseRepository->find($exercisePhaseId);
        $video = $this->videoRepository->find($videoId);

        assertNotNull($exercisePhase, 'ExercisePhase with ID ' . $exercisePhaseId . ' does not exist');
        assertNotNull($video, 'Video with ID ' . $videoId . ' does not exist');

        $exercisePhase->addVideo($video);

        $this->entityManager->persist($exercisePhase);
        $this->entityManager->flush();
    }

    /**
     * @Given I have a predefined videoCodePrototype belonging to exercise phase :exercisePhaseId and with properties
     */
    public function iHaveAPredefinedVideoCodePrototypeWithIdBelongingToExercisePhaseAndWithProperties(
        $exercisePhaseId,
        TableNode $propertyTable
    ): void
    {
        /** @var VideoAnalysisPhase $exercisePhase */
        $exercisePhase = $this->exercisePhaseRepository->find($exercisePhaseId);

        $data = $propertyTable->getHash()[0];
        $videoCodePrototype = new VideoCode($data['id']);
        $videoCodePrototype->setName($data['name']);
        $videoCodePrototype->setColor($data['color']);
        $videoCodePrototype->setExercisePhase($exercisePhase);

        $exercisePhase->addVideoCode($videoCodePrototype);

        $this->entityManager->persist($videoCodePrototype);
        $this->entityManager->persist($exercisePhase);
        $this->entityManager->flush();
    }

    /**
     * @Given I have a solution with ID :solutionId belonging to team with ID :teamId with solutionData as JSON
     */
    public function iHaveASolutionWithIdBelongingToTeamWithIdWithSolutionListsAsJson($solutionId, $teamId, PyStringNode $serverSideSolutionListsAsJSON): void
    {
        /** @var ExercisePhaseTeam $exercisePhaseTeam */
        $exercisePhaseTeam = $this->entityManager->find(ExercisePhaseTeam::class, $teamId);

        $solution = new Solution($exercisePhaseTeam, $solutionId);
        $arrayFromJson = json_decode($serverSideSolutionListsAsJSON->getRaw(), true);
        $serverSideSolutionLists = ServerSideSolutionData::fromArray($arrayFromJson);
        $solution->setSolution($serverSideSolutionLists);
        $exercisePhaseTeam->setSolution($solution);

        $this->entityManager->persist($solution);
        $this->entityManager->persist($exercisePhaseTeam);
        $this->entityManager->flush();
    }

    /**
     * @Given I have an empty solution with ID :solutionId belonging to team :teamId
     */
    public function iHaveAnEmptySolutionWithIdBelongingToTeam($solutionId, $teamId): void
    {
        /** @var ExercisePhaseTeam $exercisePhaseTeam */
        $exercisePhaseTeam = $this->entityManager->find(ExercisePhaseTeam::class, $teamId);

        $solution = new Solution($exercisePhaseTeam, $solutionId);
        $exercisePhaseTeam->setSolution($solution);

        $this->entityManager->persist($solution);
        $this->entityManager->persist($exercisePhaseTeam);
        $this->entityManager->flush();
    }

    /**
     * @Given I have an auto saved solution with ID :autoSavedSolutionId belonging to team :teamId with solutionData as JSON
     */
    public function iHaveAnAutoSavedSolutionWithIdBelongingToTeamWithSolutiondataAsJson(
        $autoSavedSolutionId,
        $teamId,
        PyStringNode $serverSideSolutionListsAsJSON
    ): void
    {
        /** @var ExercisePhaseTeam $exercisePhaseTeam */
        $exercisePhaseTeam = $this->entityManager->find(ExercisePhaseTeam::class, $teamId);
        $autosaveSolution = new AutosavedSolution($autoSavedSolutionId);
        $autosaveSolution->setTeam($exercisePhaseTeam);
        $solutionListsFromJson = json_decode($serverSideSolutionListsAsJSON->getRaw(), true);
        $serverSideSolutionLists = ServerSideSolutionData::fromArray($solutionListsFromJson);
        $autosaveSolution->setSolution($serverSideSolutionLists);

        $loggedInUser = $this->tokenStorage->getToken()->getUser();
        $autosaveSolution->setOwner($loggedInUser);

        $this->entityManager->persist($autosaveSolution);
        $this->entityManager->persist($exercisePhaseTeam);
        $this->entityManager->flush();
    }

    /**
     * @When I convert the persisted serverSideSolution for team :teamId to the clientSideSolution
     */
    public function iConvertThePersistedServersideSolutionForTeamToTheClientsideSolution($teamId): void
    {
        /** @var ExercisePhaseTeam $exercisePhaseTeam */
        $exercisePhaseTeam = $this->entityManager->find(ExercisePhaseTeam::class, $teamId);
        $exercisePhase = $exercisePhaseTeam->getExercisePhase();

        $clientSideSolutionDataBuilder = new ClientSideSolutionDataBuilder($this->exercisePhaseService);
        $this->solutionService->retrieveAndAddDataToClientSideDataBuilder(
            $clientSideSolutionDataBuilder,
            $exercisePhaseTeam,
            $exercisePhase
        );

        $this->clientSideJSON = json_encode($clientSideSolutionDataBuilder);
    }

    /**
     * @Then I get normalized client side data as JSON
     */
    public function iGetNormalizedClientSideDataAsJson(PyStringNode $expectedJSON): void
    {
        $expected = json_decode($expectedJSON->getRaw(), true);
        $actual = json_decode($this->clientSideJSON, true);

        assertEqualsCanonicalizing($expected, $actual);
    }

    /**
     * @Given The exercise phase :exercisePhaseId1 depends on the previous phase :exercisePhaseId2
     */
    public function theExercisePhaseDependsOnThePreviousPhase($exercisePhaseId1, $exercisePhaseId2): void
    {
        $exercisePhase1 = $this->exercisePhaseRepository->find($exercisePhaseId1);
        $exercisePhase2 = $this->exercisePhaseRepository->find($exercisePhaseId2);

        $exercisePhase1->setDependsOnExercisePhase($exercisePhase2);
        $exercisePhase1->setSorting(2);
        $exercisePhase2->setSorting(1);

        $this->entityManager->persist($exercisePhase1);
        $this->entityManager->persist($exercisePhase2);
        $this->entityManager->flush();
    }

    /**
     * @Given I am a member of :teamId
     */
    public function iAmAMemberOf($teamId): void
    {
        /** @var ExercisePhaseTeam $exercisePhaseTeam */
        $exercisePhaseTeam = $this->entityManager->find(ExercisePhaseTeam::class, $teamId);
        $loggedInUser = $this->tokenStorage->getToken()->getUser();

        assert($loggedInUser instanceof User, 'User not found!');

        $exercisePhaseTeam->addMember($loggedInUser);
    }

    /**
     * @When I convert the persisted serverSideSolutions for all teams of exercise phase :exercisePhaseId to the client side data
     */
    public function iConvertThePersistedServersideSolutionsForAllTeamsOfExercisePhaseToTheClientSideData($exercisePhaseId): void
    {
        $exercisePhase = $this->exercisePhaseRepository->find($exercisePhaseId);
        $exercisePhaseTeams = $exercisePhase->getTeams()->toArray();

        $clientSideSolutionDataBuilder = new ClientSideSolutionDataBuilder($this->exercisePhaseService);
        $this->solutionService->retrieveAndAddDataToClientSideDataBuilderForSolutionView(
            $clientSideSolutionDataBuilder,
            $exercisePhaseTeams
        );

        $this->clientSideJSON = json_encode($clientSideSolutionDataBuilder);
    }

    /**
     * @Given User :username belongs to :teamId
     */
    public function userBelongsTo($username, $teamId): void
    {
        /** @var ExercisePhaseTeam $exercisePhaseTeam */
        $exercisePhaseTeam = $this->entityManager->find(ExercisePhaseTeam::class, $teamId);
        $user = $this->getUserByEmail($username);

        $exercisePhaseTeam->addMember($user);
    }

    /**
     * @Given The User :username is member of ExercisePhaseTeam :teamId
     */
    public function ensureTheUserIsMemberOfExercisePhaseTeam($username, $teamId): void
    {
        /** @var ExercisePhaseTeam $team */
        $team = $this->entityManager->find(ExercisePhaseTeam::class, $teamId);
        $user = $this->getUserByEmail($username);

        $team->addMember($user);
        $team->setCreator($user);

        $this->entityManager->persist($team);
        $this->entityManager->flush();
    }

    /**
     * @Given The ExercisePhaseTeam :teamId has a Solution :solutionId
     */
    public function ensureTheExercisePhaseTeamHasASolution($teamId, $solutionId): void
    {
        /** @var ExercisePhaseTeam $team */
        $team = $this->entityManager->find(ExercisePhaseTeam::class, $teamId);
        /** @var Solution $solution */
        $solution = $this->entityManager->find(Solution::class, $solutionId);

        if (!$solution) {
            $solution = new Solution($team, $solutionId);
            $this->entityManager->persist($solution);
        }

        $team->setSolution($solution);

        $this->entityManager->persist($team);
        $this->entityManager->flush();
    }

    /**
     * @Given The User :username has created an AutosavedSolution :autosavedSolutionId for ExercisePhaseTeam :teamId
     */
    public function ensureTheUserHasCreatedAnAutosavedSolutionForExerciseTeam($username, $autosavedSolutionId, $teamId): void
    {
        $user = $this->getUserByEmail($username);
        /** @var ExercisePhaseTeam $team */
        $team = $this->entityManager->find(ExercisePhaseTeam::class, $teamId);
        /** @var AutosavedSolution $autosavedSolution */
        $autosavedSolution = $this->entityManager->find(AutosavedSolution::class, $autosavedSolutionId);

        if (!$autosavedSolution) {
            $autosavedSolution = new AutosavedSolution($autosavedSolutionId);
        }

        $autosavedSolution->setOwner($user);
        $team->addAutosavedSolution($autosavedSolution);

        $this->entityManager->persist($autosavedSolution);
        $this->entityManager->persist($team);
        $this->entityManager->flush();
    }

    /**
     * @Then No ExercisePhaseTeam created by User :username should exist
     */
    public function assertExercisePhaseTeamsCreatedByUserDoNotExist($username): void
    {
        $allTeams = $this->entityManager->getRepository(ExercisePhaseTeam::class)->findAll();
        $teamsCreatedByUser = array_filter($allTeams, function (ExercisePhaseTeam $team) use ($username) {
            return $team->getCreator()->getUsername() === $username;
        });

        assertEquals(0, count($teamsCreatedByUser));
    }

    /**
     * @Then No AutosavedSolution of User :username does exist
     */
    public function assertAutosavedSolutionsOfUserDoNotExist($username): void
    {
        $allAutosavedSolutions = $this->entityManager->getRepository(AutosavedSolution::class)->findAll();
        $autosavedSolutionsOfUser = array_filter($allAutosavedSolutions, function (AutosavedSolution $autosavedSolution) use ($username) {
            return $autosavedSolution->getOwner()->getUsername() === $username;
        });

        assertEquals(0, count($autosavedSolutionsOfUser));
    }


    public function createExercisePhase(array $phaseData): ExercisePhase
    {
        $phaseType = ExercisePhaseType::from($phaseData['type']);

        $phase = ExercisePhase::byType($phaseType, $phaseData['id']);

        $phase->setName($phaseData['name']);
        $phase->setTask($phaseData['task']);
        $phase->setIsGroupPhase($phaseData['isGroupPhase'] === "true");
        $phase->setSorting(intval($phaseData['sorting']));
        $phase->setOtherSolutionsAreAccessible($phaseData['otherSolutionsAreAccessible'] === "true");

        /** @var Exercise $exercise */
        $exercise = $this->entityManager->find(Exercise::class, $phaseData['belongsToExercise']);
        $phase->setBelongsToExercise($exercise);
        $exercise->addPhase($phase);

        if ($phaseData['dependsOnPhase'] !== null) {
            /** @var ExercisePhase $phaseDependingOn */
            $phaseDependingOn = $this->entityManager->find(ExercisePhase::class, $phaseData['dependsOnPhase']);
            $phase->setDependsOnExercisePhase($phaseDependingOn);
        }

        // phase type specific
        switch ($phaseType) {
            case ExercisePhaseType::VIDEO_ANALYSIS:
                $phase->setVideoAnnotationsActive($phaseData['videoAnnotationsActive'] === "true");
                $phase->setVideoCodesActive($phaseData['videoCodesActive'] === "true");
                break;
            case ExercisePhaseType::VIDEO_CUT:
            case ExercisePhaseType::REFLEXION:
            case ExercisePhaseType::MATERIAL:
                break;
        }

        $this->entityManager->persist($phase);
        $this->entityManager->flush();

        return $phase;
    }


    public function assertExercisePhaseTeamForUserExists(User $user, ExercisePhase $exercisePhase): void
    {
        $exercisePhaseTeam = $this->exercisePhaseTeamRepository->findByMemberAndExercisePhase($user, $exercisePhase);

        assertNotNull($exercisePhaseTeam);
    }

    /**
     * @Given An ExercisePhase with the following data exists:
     */
    public function assureExercisePhasesWithTheFollowingDataExist(TableNode $tableNode): void
    {
        foreach ($tableNode->getHash() as $phaseData) {
            $this->createExercisePhase($phaseData);
        }
    }

    /**
     * @Given An ExercisePhase with the following json-data exists:
     *
     * This is just another way to use the step above, because oftentimes configuring
     * the step via JSON is much more convenient and easier to read for large data sets.
     */
    public function assureAnExercisePhaseWithTheFollowingJsonDataExists(PyStringNode $exercisePhaseDataJson): void
    {
        $phases = json_decode($exercisePhaseDataJson->getRaw(), true);
        foreach ($phases as $phaseData) {
            $this->createExercisePhase($phaseData);
        }
    }

    /**
     * @When I delete the exercise phase :exercisePhaseId
     */
    public function iDeleteTheExercisePhase($exercisePhaseId): void
    {
        $exercisePhase = $this->exercisePhaseRepository->find($exercisePhaseId);
        assertEquals($exercisePhaseId, $exercisePhase->getId());
        $this->exercisePhaseService->deleteExercisePhase($exercisePhase);
    }

    /**
     * @Then The exercise phase :exercisePhaseId is deleted
     */
    public function theExercisePhaseShouldIsDeleted($exercisePhaseId): void
    {
        $exercisePhase = $this->exercisePhaseRepository->find($exercisePhaseId);
        assertEquals(null, $exercisePhase);
    }

    /**
     * @Then The ExercisePhase :exercisePhaseId exists
     */
    public function theExercisePhaseExists($exercisePhaseId): void
    {
        $exercisePhase = $this->exercisePhaseRepository->find($exercisePhaseId);
        assertNotNull($exercisePhase);
    }

    /**
     * @Then The ExercisePhase :exercisePhaseId does not exist
     */
    public function theExercisePhaseDoesNotExist($exercisePhaseId): void
    {
        $exercisePhase = $this->exercisePhaseRepository->find($exercisePhaseId);
        assertEquals(null, $exercisePhase);
    }

    /**
     * @Then The team :teamId is deleted
     */
    public function theExercisePhaseTeamShouldIsDeleted($teamId): void
    {
        $exercisePhaseTeam = $this->exercisePhaseTeamRepository->find($teamId);
        assertEquals(null, $exercisePhaseTeam);
    }

    /**
     * @Then The ExercisePhaseTeam :teamId exists
     */
    public function theExercisePhaseTeamExists($teamId): void
    {
        $exercisePhaseTeam = $this->exercisePhaseTeamRepository->find($teamId);
        assertNotNull($exercisePhaseTeam);
    }


    /**
     * @Then The Solution :solutionId is deleted
     */
    public function theSolutionShouldIsDeleted($solutionId): void
    {
        $solution = $this->solutionRepository->find($solutionId);
        assertEquals(null, $solution);
    }

    /**
     * @Then The Solution :solutionId exists
     */
    public function theSolutionExists($solutionId): void
    {
        $solution = $this->solutionRepository->find($solutionId);
        assertNotNull($solution);
    }

    /**
     * @Then :amount exercise phases should exist
     */
    public function exercisePhasesShouldExist($amount): void
    {
        $exercisePhases = $this->exercisePhaseRepository->findAll();
        assertEquals($amount, count($exercisePhases));
    }

    /**
     * @When I upload material :fileName to the phase
     */
    public function IUploadMaterialToThePhase($fileName): void
    {
        // working path is the /e2e-testrunner directory
        // we mount this path into the container when the tests run inside the gitlab pipeline
        $fixturePath = './FileUploadFixtures';

        $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS
                const [fileChooser] = await Promise.all([
                    // It is important to call waitForEvent before click to set up waiting.
                    vars.page.waitForEvent('filechooser'),
                    // Opens the file chooser.
                    vars.page.click('text=Hier klicken um Anhänge hochzuladen')
                ])
                await fileChooser.setFiles(`$fixturePath/$fileName`);
            JS
        );
    }

    /**
     * @Then I should see the uploaded file :fileName in the attachment list
     */
    public function iShouldSeeTheUploadedFileInTheAttachmentList($expectedFileName): void
    {
        $actualFileName = $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS
                const attachment = await vars.page.locator('.attachment-list .attachment-list__name').first()
                try {
                    await expect(attachment).toHaveText(`$expectedFileName`)
                } catch (e) {
                  // just catch the error
                }
                return await attachment.textContent();
            JS
        );

        assertEquals($expectedFileName, $actualFileName, 'The uploaded file is not in the attachment list');
    }

    /**
     * @Then The upload area shows :amount file
     */
    public function theUploadAreaShowsFile($expectedAmount): void
    {
        $actualFileCount = $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS
                const fileCount = await vars.page.locator('.dropzone .dz-image-preview').count()
                return fileCount;
            JS
        );

        assertEquals($expectedAmount, $actualFileCount, 'The upload area does not show the expected amount of files');
    }

    /**
     * @When I remove the uploaded file :fileName
     */
    public function iRemoveTheUploadedFile($fileName): void
    {
        $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS
                // find attachment by name
                const attachment = vars.page.locator(`.attachment-list li:has-text('$fileName')`);
                // click button with text "Datei löschen"
                await attachment.locator('.button:has-text("Datei löschen")').click();
            JS
        );
    }

    /**
     * @Then I should see :amount uploaded files in the attachment list
     */
    public function iShouldSeeUploadedFilesInTheAttachmentList($expectedAmount): void
    {
        $actualFileCount = $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS
                const attachments = await vars.page.locator('.attachment-list li')
                try {
                    await expect(attachments).toHaveCount(`$expectedAmount`);
                } catch (e) {
                    // just catch error
                }
                return await attachments.count();
            JS
        );

        assertEquals($expectedAmount, $actualFileCount, 'The attachment list does not show the expected amount of files');
    }

    /**
     * @Given An ExercisePhaseTeam :teamId belonging to exercise phase :phaseId created by :username exists
     */
    public function anExercisePhaseTeamBelongingToExercisePhaseCreatedByExists(string $teamId, string $phaseId, string $username): void
    {
        $exercisePhase = $this->exercisePhaseRepository->find($phaseId);
        $user = $this->getUserByEmail($username);

        $exercisePhaseTeam = new ExercisePhaseTeam($teamId);
        $exercisePhaseTeam->setCreator($user);
        $exercisePhaseTeam->addMember($user);
        $exercisePhaseTeam->setExercisePhase($exercisePhase);

        $this->entityManager->persist($exercisePhaseTeam);
        $this->entityManager->flush();
    }

    /**
     * @Then The creator of ExercisePhaseTeam :teamId should be :username
     */
    public function theCreatorOfExercisePhaseTeamShouldBe(string $teamId, string $username): void
    {
        $exercisePhaseTeam = $this->exercisePhaseTeamRepository->find($teamId);
        $user = $this->getUserByEmail($username);

        assertEquals($user, $exercisePhaseTeam->getCreator());
    }

    /**
     * @Then The creator of ExercisePhaseTeam :teamId should not be :username
     */
    public function theCreatorOfExercisePhaseTeamShouldNotBe(string $teamId, string $username): void
    {
        $exercisePhaseTeam = $this->exercisePhaseTeamRepository->find($teamId);
        $creator = $exercisePhaseTeam->getCreator();

        assertNotEquals($username, $creator->getUsername());
    }

    /**
     * @Then The Solution with ID :solutionId does not exist
     */
    public function theSolutionWithIDDoesNotExist(string $solutionId): void
    {
        $solution = $this->solutionRepository->find($solutionId);
        assertEquals(null, $solution);
    }
}
