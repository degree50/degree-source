@fixtures @exercise-phase-status @exercise-status
Feature: Track phase and exercise status through editing process

    Background:
        Given A Course with ID "course1" exists
        And an exercise with id "exercise1" in course "course1" exists
        And The exercise "exercise1" has these phases:
            | id          | type          | isGroupPhase |
            | analysis1   | videoAnalysis | no           |
            | cut1        | videoCutting  | no           |
            | groupPhase1 | videoCutting  | yes          |
            | reflexion1  | reflexion     | no           |
            | material1   | material      | no           |
            | material2   | material      | no           |

    ##########
    # PHASES #
    ##########

    @integration
    Scenario: The student has not yet started the phase of a non group phase
        Given I am a student working on "exercise1"
        When I have not yet started an exercise phase of "exercise1"
        Then The derived exercise phase status of "analysis1" should be "INITIAL" for the student

    @integration
    Scenario: The student is not yet part of a group of a group phase
        Given I am a student working on "exercise1"
        When I am not part of a group for a group exercise phase
        Then The derived exercise phase status of "groupPhase1" should be "INITIAL" for the student

    @integration
    Scenario: The student enters a group of a group phase
        Given I am a student working on "exercise1"
        When I enter a group in "groupPhase1"
        Then The students exercise phase status of "groupPhase1" should be "IN_BEARBEITUNG"

    @integration
    Scenario: The student starts the phase
        Given I am a student working on "exercise1"
        When I start "analysis1" for the first time
        Then The exercise phase status should be "IN_BEARBEITUNG"

    @integration @material
    Scenario Outline: The student finishes a phase
        Given I am a student working on "exercise1"
        And I am working on a phase "<phaseId>" where a review is required: "<reviewIsRequired>"
        When I finish phase "<phaseId>"
        Then The phase status of "<phaseId>" should be "<phaseStatus>"

        Examples:
            | phaseId    | phaseStatus | reviewIsRequired |
            | analysis1  | BEENDET     | no               |
            | cut1       | BEENDET     | no               |
            | reflexion1 | BEENDET     | no               |
            | material1  | BEENDET     | no               |
            | material2  | IN_REVIEW   | yes              |

    @integration @material
    Scenario: A student finishes a material phase which does need no review
        Given I am a student working on "exercise1"
        And I am working on a phase "material1" where a review is required: "no"
        When I finish phase "material1"
        Then The phase status of "material1" should be "BEENDET"
        And A copy of the material from "material1" should be added to the "Schreibtisch" of each user who was part of the Group which created the solution

    @integration @material
    Scenario: A dozent finishes the review of a material phase
        Given I am a dozent in course "course1"
        And material phase "material1" requires a review: "yes"
        When I finish the review of a solution of a material phase "material1"
        Then The phase status of "material1" should be "BEENDET"
        And A copy of the material from "material1" should be added to the "Schreibtisch" of each user who was part of the Group which created the solution

    @integration
    Scenario: A student re-visits an already finished exercise phase
        Given I am a student working on "exercise1"
        When I open an exercise phase "analysis1" with status "BEENDET"
        Then The students exercise phase status of "analysis1" should be "IN_BEARBEITUNG"

    @integration @material
    Scenario: A student re-visits an already finished exercise phase of type material
        Given I am a student working on "exercise1"
        When I open an exercise phase "material1" with status "BEENDET"
        Then The exercise phase status should be "BEENDET"

    @integration @material
    Scenario: A student re-visits an exercise phase of type material which is currently being reviewed
        Given I am a student working on "exercise1"
        When I open an exercise phase "material1" with status "IN_REVIEW"
        Then The exercise phase status should be "IN_REVIEW"

    ############
    # Exercise #
    ############

    @integration
    Scenario: The exercise has not been started by the student, yet
        Given I am a student working on "exercise1"
        When I have not yet started an exercise phase of "exercise1"
        Then The derived exercise status of "exercise1" should be "NEU"

    @integration
    Scenario: The student has at least one phase of an exercise "IN_BEARBEITUNG"
        Given I am a student working on "exercise1"
        When I start "analysis1" for the first time
        Then The derived exercise status of "exercise1" should be "IN_BEARBEITUNG"

    @integration
    Scenario: The student has one finished phase but the others are still unopened
        Given I am a student working on "exercise1"
        And I am working on a phase "analysis1" where a review is required: "no"
        When I finish phase "analysis1"
        Then The derived exercise status of "exercise1" should be "IN_BEARBEITUNG"

    @integration
    Scenario: The student has a solution for all phases but one is still "IN_REVIEW"
        Given I am a student working on "exercise1"
        When I have finished all phases of exercise "exercise1"
        And the material phase "material1" is "IN_REVIEW"
        Then The derived exercise status of "exercise1" should be "IN_BEARBEITUNG"

    @integration
    Scenario: The student has finished all phases of an exercise
        Given I am a student working on "exercise1"
        When I have finished all phases of exercise "exercise1"
        Then The derived exercise status of "exercise1" should be "BEENDET"
