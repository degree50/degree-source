@fixtures @playwright
Feature: When a student solves an exercise they should only be able to access phases, where all predecessors either have a solution or are reflexionPhases

    Background:
        Given A User "test-admin@sandstorm.de" with the role "ROLE_ADMIN" exists
        Given A User "test-student@sandstorm.de" with the role "ROLE_STUDENT" exists
        And A Course with ID "course1" exists
        And An Exercise with the following json-data exists:
        """
        {
            "id": "e1",
            "name": "exercise1",
            "description": "description of exercise1",
            "creator": "test-admin@sandstorm.de",
            "course": "course1",
            "status": 2
        }
        """
        And Course "course1" belongs to exercise "e1"

        And An ExercisePhase with the following json-data exists:
        """
        [
           {
               "id": "analysis1",
               "name": "Analysis1",
               "task": "Description of Analysis1",
               "isGroupPhase": "false",
               "sorting": "0",
               "otherSolutionsAreAccessible": "true",
               "belongsToExercise": "e1",
               "dependsOnPhase": "null",
               "type": "videoAnalysis",
               "videoAnnotationsActive": "true",
               "videoCodesActive": "true"
           },
           {
               "id": "reflexion1",
               "name": "Reflexion1",
               "task": "Description of Reflexion1",
               "isGroupPhase": "false",
               "sorting": "1",
               "otherSolutionsAreAccessible": "true",
               "belongsToExercise": "e1",
               "dependsOnPhase": "analysis1",
               "type": "reflexion",
               "videoAnnotationsActive": "",
               "videoCodesActive": ""
           },
           {
               "id": "cutting1",
               "name": "Cutting1",
               "task": "Description of Cutting1",
               "isGroupPhase": "true",
               "sorting": "2",
               "otherSolutionsAreAccessible": "true",
               "belongsToExercise": "e1",
               "dependsOnPhase": "null",
               "type": "videoCutting",
               "videoAnnotationsActive": "",
               "videoCodesActive": ""
           },
           {
               "id": "reflexion2",
               "name": "Reflexion2",
               "task": "Description of Reflexion2",
               "isGroupPhase": "true",
               "sorting": "3",
               "otherSolutionsAreAccessible": "true",
               "belongsToExercise": "e1",
               "dependsOnPhase": "cutting1",
               "type": "reflexion",
               "videoAnnotationsActive": "",
               "videoCodesActive": ""
           }
        ]
        """

    # Phase 1 analysis1 setup
    And I have a team with ID "team1" belonging to exercise phase "analysis1" and creator "test-student@sandstorm.de"
    And User "test-student@sandstorm.de" belongs to "team1"
    And The User "test-student@sandstorm.de" has CourseRole "STUDENT" in Course "course1"
    And I have a solution with ID "annotationSolution1" belonging to team with ID "team1" with solutionData as JSON
    """
    {
      "annotations": [
        {
          "start": "00:01:03.315",
          "end": "00:01:30.000",
          "text": "Annotation 1",
          "memo": "",
          "color": null
        }
      ],
      "videoCodes": [ ],
      "customVideoCodesPool": [ ],
      "cutList": [ ]
    }
    """

    Scenario: The first phase "analysis1" should be accessible
        When I am logged in via browser as "test-student@sandstorm.de"
        And I visit url "/exercise/e1/show"
        And I select the nth "0" element with testid "exercisePhaseSidebarItem"
        Then The selected element should not have the CSS-class "sidebar-item--disabled"

    Scenario: The second phase "reflexion1" should be accessible because "analysis1" has a solution
        When I am logged in via browser as "test-student@sandstorm.de"
        And I visit url "/exercise/e1/show"
        And I select the nth "1" element with testid "exercisePhaseSidebarItem"
        Then The selected element should not have the CSS-class "sidebar-item--disabled"

    Scenario: The third phase "cutting" should be accessible because "analysis1" has a solution and "reflexion1" is a reflexionPhase
        When I am logged in via browser as "test-student@sandstorm.de"
        And I visit url "/exercise/e1/show"
        And I select the nth "2" element with testid "exercisePhaseSidebarItem"
        Then The selected element should not have the CSS-class "sidebar-item--disabled"

    Scenario: The fourth phase "reflexion2" should not be accessible because "cutting1" does not have a solution and "reflexion2" depends on it
        When I am logged in via browser as "test-student@sandstorm.de"
        And I visit url "/exercise/e1/show"
        And I select the nth "3" element with testid "exercisePhaseSidebarItem"
        Then The selected element should have the CSS-class "sidebar-item--disabled"
