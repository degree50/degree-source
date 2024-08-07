@fixtures
Feature: Test deleting of an exercise and exercise phases

    Background:
        Given I am logged in as "foo@bar.de"
        And A Course with ID "course1" exists
        And I have an exercise with ID "exercise1" belonging to course "course1"
        And an exercise with id "exercise1" in course "course1" exists
        And An Exercise with ID "exercise1" created by User "foo@bar.de" in Course "course1" exists
        And The exercise "exercise1" has these phases:
            | id          | type          | isGroupPhase | dependsOn   |
            | analysis1   | videoAnalysis | no           |             |
            | groupPhase1 | videoCutting  | yes          | analysis1   |

    @integration
    Scenario: Delete exercise
        When I delete the exercise "exercise1"
        Then The exercise "exercise1" is deleted

    @integration
    Scenario: Delete exercise phase with no dependencies
        When I delete the exercise phase "groupPhase1"
        Then The exercise phase "groupPhase1" is deleted
        And 1 exercise phases should exist

    @integration
    Scenario: Delete exercise phase with dependencies
        When I delete the exercise phase "analysis1"
        Then The exercise phase "analysis1" is deleted
        # the phase depending on "analysis1" should not be deleted
        And 1 exercise phases should exist
