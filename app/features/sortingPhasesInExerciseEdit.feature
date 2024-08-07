@fixtures @playwright
Feature: Editing of phase order inside an Exercise

    # Phases can be sorted with the following constraints
    # * the first phase can't be moved 'up'
    # * the last phase can't be moved 'down'
    # * a phase depending on another phase, can't be moved before the phase it depends on
    # * a phase which has phases depending on it, cannot be moved behind any of these phases

    Background:
        Given A User "test-admin@sandstorm.de" with the role "ROLE_ADMIN" exists
        And A Course with ID "course1" exists
        And An Exercise with the following data exists:
            | id | name      | description              | creator                 | course  |
            | e1 | exercise1 | description of exercise1 | test-admin@sandstorm.de | course1 |

        And An ExercisePhase with the following data exists:
            | id         | name       | task                      | isGroupPhase | sorting | otherSolutionsAreAccessible | belongsToExercise | dependsOnPhase | type          | videoAnnotationsActive | videoCodesActive |
            | analysis1  | Analysis1  | Description of Analysis1  | false        | 0       | true                        | e1                | null           | videoAnalysis | true                   | true             |
            | cutting1   | Cutting1   | Description of Cutting1   | true         | 1       | true                        | e1                | null           | videoCutting  |                        |                  |
            | reflexion1 | Reflexion1 | Description of Reflexion1 | false        | 2       | true                        | e1                | cutting1       | reflexion     |                        |                  |
            | analysis2  | Analysis2  | Description of Analysis2  | true         | 3       | true                        | e1                | null           | videoAnalysis | true                   | true             |
            | reflexion2 | Reflexion2 | Description of Reflexion2 | true         | 4       | true                        | e1                | cutting1       | reflexion     |                        |                  |
            | cutting2   | Cutting2   | Description of Cutting2   | false        | 5       | true                        | e1                | null           | videoCutting  |                        |                  |

    Scenario: The first phase can't be moved 'up'
        When I am logged in via browser as "test-admin@sandstorm.de"
        And I visit url "/exercise/e1/edit"
        And I select the nth "0" element with testId "movePhaseUp"
        Then the selected element should have its attribute "disabled" set to value "disabled"

    Scenario: The last phase can't be moved 'down'
        When I am logged in via browser as "test-admin@sandstorm.de"
        And I visit url "/exercise/e1/edit"
        And I select the nth "5" element with testId "movePhaseDown"
        Then the selected element should have its attribute "disabled" set to value "disabled"

    Scenario: A phase depending on another can't be moved 'up' before the phase it depends on
        When I am logged in via browser as "test-admin@sandstorm.de"
        And I visit url "/exercise/e1/edit"
        And I select the nth "2" element with testId "movePhaseUp"
        Then the selected element should have its attribute "disabled" set to value "disabled"

    Scenario: A phase which has a phase depending on it, can't be moved 'down' behind this phase
        When I am logged in via browser as "test-admin@sandstorm.de"
        And I visit url "/exercise/e1/edit"
        And I select the nth "1" element with testId "movePhaseDown"
        Then the selected element should have its attribute "disabled" set to value "disabled"
