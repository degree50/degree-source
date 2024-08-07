@fixtures @playwright
Feature: A reflexionPhase should show solutions of the phase it depends on

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
           },
           {
               "id": "analysis2",
               "name": "Analysis2",
               "task": "Description of Analysis2",
               "isGroupPhase": "true",
               "sorting": "4",
               "otherSolutionsAreAccessible": "true",
               "belongsToExercise": "e1",
               "dependsOnPhase": "null",
               "type": "videoAnalysis",
               "videoAnnotationsActive": "true",
               "videoCodesActive": "true"
           },
           {
               "id": "cutting2",
               "name": "Cutting2",
               "task": "Description of Cutting2",
               "isGroupPhase": "false",
               "sorting": "5",
               "otherSolutionsAreAccessible": "true",
               "belongsToExercise": "e1",
               "dependsOnPhase": "null",
               "type": "videoCutting",
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

    # Phase 3 - cutting1 setup
    And I have a team with ID "team2" belonging to exercise phase "cutting1" and creator "test-student@sandstorm.de"
    And User "test-student@sandstorm.de" belongs to "team2"
    And The User "test-student@sandstorm.de" has CourseRole "STUDENT" in Course "course1"
    And I have a solution with ID "cutting1Solution" belonging to team with ID "team2" with solutionData as JSON
    """
    {
      "annotations": [ ],
      "videoCodes": [ ],
      "customVideoCodesPool": [ ],
      "cutList": [
          {
              "start": "00:01:03.315",
              "end": "00:01:30.000",
              "text": "Cut 1",
              "memo": "",
              "color": null,
              "offset": 0,
              "playbackRate": "1"
          }
      ]
    }
    """

    Scenario: ReflexionPhase shows correct solution view (analysis -> videoEditor)
        When I am logged in via browser as "test-student@sandstorm.de"
        And I visit url "/exercise/e1/show"
        And I select the nth "1" element with testid "exercisePhaseSidebarItem"
        And I click on the selected element
        And I click on first element with testId "startPhaseButtonSingle"
        And I select the nth "0" element with testid "videoEditor"
        Then "1" elements of selectedElement type should exist

    Scenario: ReflexionPhase shows correct solution view (cutting -> cutting solution)
        When I am logged in via browser as "test-student@sandstorm.de"
        And I visit url "/exercise/e1/show"
        And I select the nth "3" element with testid "exercisePhaseSidebarItem"
        And I click on the selected element
        And I click on first element with testId "createGroupButton"
        And I click on first element with testId "startPhaseButtonGroup"
        And I select the nth "0" element with testid "cuttingSolutions"
        Then "1" elements of selectedElement type should exist
