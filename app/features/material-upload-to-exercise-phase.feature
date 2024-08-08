@fixtures @playwright
Feature: Upload material to exercise phase
    Background:
        Given A User "test-admin@sandstorm.de" with the role "ROLE_ADMIN" exists
        And A Course with ID "course1" exists
        And An Exercise with the following data exists:
            | id | name      | description              | creator                 | course  |
            | e1 | exercise1 | description of exercise1 | test-admin@sandstorm.de | course1 |

        And An ExercisePhase with the following data exists:
            | id         | name       | task                      | isGroupPhase | sorting | otherSolutionsAreAccessible | belongsToExercise | dependsOnPhase | type          | videoAnnotationsActive | videoCodesActive |
            | analysis1  | Analysis1  | Description of Analysis1  | false        | 0       | true                        | e1                | null           | videoAnalysis | true                   | true             |

    @debug
    Scenario: I upload material to phase "analysis1"
        When I am logged in via browser as "test-admin@sandstorm.de"
        And I visit url "/exercise/e1/edit"
        When I click on first element with testId 'exercisePhaseEditButton'
        And I upload material "cat.jpg" to the phase
        Then I should see the uploaded file "cat.jpg" in the attachment list
        And The upload area shows 1 file
        And I should see 1 uploaded files in the attachment list
        When I remove the uploaded file "cat.jpg"
        Then I wait for the event "attachment-deleted" to be dispatched
        And I should see 0 uploaded files in the attachment list
        And The upload area shows 0 file
