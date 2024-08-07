@fixtures @playwright
Feature: Valid choices for "dependsOnExercisePhase" when editing ExercisePhases

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
            | reflexion1 | Reflexion1 | Description of Reflexion1 | false        | 2       | true                        | e1                | null           | reflexion     |                        |                  |
            | analysis2  | Analysis2  | Description of Analysis2  | true         | 3       | true                        | e1                | null           | videoAnalysis | true                   | true             |
            | cutting2   | Cutting2   | Description of Cutting2   | false        | 4       | true                        | e1                | null           | videoCutting  |                        |                  |
            | reflexion2 | Reflexion2 | Description of Reflexion2 | true         | 5       | true                        | e1                | null           | reflexion     |                        |                  |

    Scenario: First ExercisePhase can not depend on any other ExercisePhase
        When I am logged in via browser as "test-admin@sandstorm.de"
        And I visit url "/exercise/e1/edit/phase/analysis1/edit"
        # These texts are all options of the 'dependsOnExercisePhase' select
        Then the page contains all the following texts:
            | Keine |
        And the page contains none of the following texts:
            # WHY: The Name of the currently edited Phase is present in the headline
            # | Analysis1  |
            | Cutting1   |
            | Reflexion1 |
            | Analysis2  |
            | Cutting2   |
            | Reflexion2 |

    Scenario: Second ExercisePhase is a VideoCuttingPhase and can only depend on the first ExercisePhase because it's a VideoAnalysisPhase
        When I am logged in via browser as "test-admin@sandstorm.de"
        And I visit url "/exercise/e1/edit/phase/cutting1/edit"
        # These texts are all options of the 'dependsOnExercisePhase' select
        Then the page contains all the following texts:
            | Keine     |
            | Analysis1 |
        And the page contains none of the following texts:
            # WHY: The Name of the currently edited Phase is present in the headline
            # | Cutting1   |
            | Reflexion1 |
            | Analysis2  |
            | Cutting2   |
            | Reflexion2 |

    Scenario: Third ExercisePhase is a ReflexionPhase and can only depend on the first two ExercisePhases
        When I am logged in via browser as "test-admin@sandstorm.de"
        And I visit url "/exercise/e1/edit/phase/reflexion1/edit"
        # These texts are all options of the 'dependsOnExercisePhase' select
        Then the page contains all the following texts:
            | Keine     |
            | Analysis1 |
            | Cutting1  |
        And the page contains none of the following texts:
            # WHY: The Name of the currently edited Phase is present in the headline
            # | Reflexion1 |
            | Analysis2  |
            | Cutting2   |
            | Reflexion2 |

    Scenario: Fourth ExercisePhase is a VideoAnalysisPhase and can only depend on another VideoAnalysisPhase
        When I am logged in via browser as "test-admin@sandstorm.de"
        And I visit url "/exercise/e1/edit/phase/analysis2/edit"
        # These texts are all options of the 'dependsOnExercisePhase' select
        Then the page contains all the following texts:
            | Analysis1 |
        And the page contains none of the following texts:
            | Cutting1  |
            | Reflexion1 |
            # WHY: The Name of the currently edited Phase is present in the headline
            # | Analysis2  |
            | Cutting2   |
            | Reflexion2 |

    Scenario: Fifth ExercisePhase is a VideoCuttingPhase and can only depend on the two VideoAnalysisPhases sorted before it
        When I am logged in via browser as "test-admin@sandstorm.de"
        And I visit url "/exercise/e1/edit/phase/cutting2/edit"
        # These texts are all options of the 'dependsOnExercisePhase' select
        Then the page contains all the following texts:
            | Keine     |
            | Analysis1 |
            | Analysis2  |
        And the page contains none of the following texts:
            | Cutting1  |
            | Reflexion1 |
            # WHY: The Name of the currently edited Phase is present in the headline
            # | Cutting2   |
            | Reflexion2 |

    Scenario: Last ExercisePhase is a ReflexionPhase and can depend on any other ExercisePhase sorted before it except ReflexionPhases
        When I am logged in via browser as "test-admin@sandstorm.de"
        And I visit url "/exercise/e1/edit/phase/reflexion2/edit"
        # These texts are all options of the 'dependsOnExercisePhase' select
        Then the page contains all the following texts:
            | Keine     |
            | Analysis1 |
            | Cutting1  |
            | Analysis2  |
            | Cutting2   |
        And the page contains none of the following texts:
            | Reflexion1 |
            # WHY: The Name of the currently edited Phase is present in the headline
            # | Reflexion2 |
