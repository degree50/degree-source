@fixtures @playwright
Feature: Deleting ExercisePhaseTeams

    Background:
        Given A User "test-dozent@sandstorm.de" with the role "ROLE_ADMIN" exists
        And A User "test-student1@sandstorm.de" with the role "ROLE_STUDENT" exists
        And A User "test-student2@sandstorm.de" with the role "ROLE_STUDENT" exists

        And A Course with ID "course" exists
        And An Exercise with ID "exercise" created by User "test-dozent@sandstorm.de" in Course "course" exists
        And An ExercisePhase with the following data exists:
            | id            | name | task                         | isGroupPhase | sorting | otherSolutionsAreAccessible | belongsToExercise | dependsOnPhase | type          | videoAnnotationsActive | videoCodesActive |
            | exercisePhase | Test | Description of ExercisePhase | true         | 0       | true                        | exercise          | null           | videoAnalysis | true                   | true             |

        And Exercise "exercise" is published
        And The User "test-student1@sandstorm.de" has CourseRole "STUDENT" in Course "course"
        And The User "test-student2@sandstorm.de" has CourseRole "STUDENT" in Course "course"

    Scenario: Last user can delete ExercisePhaseTeam
        Given I am logged in via browser as "test-student1@sandstorm.de"
        And I click on "exercise"
        And I click on "Aufgabe Starten"
        And I click on "Neue Gruppe anlegen"
        And I click on "Gruppe löschen"

        Then the page should contain the text "Gruppe gelöscht"

    Scenario: User can leave ExercisePhaseTeam
        Given I am logged in via browser as "test-student1@sandstorm.de"
        And I click on "exercise"
        And I click on "Aufgabe Starten"
        And I click on "Neue Gruppe anlegen"

        And I am not logged in
        And I am logged in via browser as "test-student2@sandstorm.de"
        And I click on "exercise"
        And I click on "Aufgabe Starten"
        And I click on "Gruppe beitreten"

        And I click on "Gruppe verlassen"

        Then the page should contain the text "Gruppe verlassen"
        And the page should contain the text "Gruppe beitreten"
