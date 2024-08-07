@fixtures @integration
Feature: Degree User is removed completely from system

    Background:
        Given A User "admin@test.de" with the role "ROLE_ADMIN" exists
        And A User "dozent@test.de" with the role "ROLE_DOZENT" exists
        And A User "student1@test.de" with the role "ROLE_STUDENT" exists
        And A User "student2@test.de" with the role "ROLE_STUDENT" exists
        And A Course with ID "course1" exists

    Scenario: Remove Student with Exercise
        Given The User "student1@test.de" has CourseRole "DOZENT" in Course "course1"
        And An Exercise with ID "exerciseByStudent1" created by User "student1@test.de" in Course "course1" exists
        And An ExercisePhase with the following data exists:
            | id            | name | task                         | isGroupPhase | sorting | otherSolutionsAreAccessible | belongsToExercise  | dependsOnPhase | type          | videoAnnotationsActive | videoCodesActive |
            | exercisePhase | Test | Description of ExercisePhase | true         | 0       | true                        | exerciseByStudent1 | null           | videoAnalysis | true                   | true             |

        And A Video with Id "video1" created by User "student1@test.de" exists

        And I have a team with ID "team" belonging to exercise phase "exercisePhase" and creator "student1@test.de"
        And I have a solution with ID "solution" belonging to team with ID "team" with solutionData as JSON
        """
            {
              "annotations": [],
              "videoCodes": [],
              "customVideoCodesPool": [],
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
        And A CutVideo with Id "cutVideo" of Video "video1" belonging to Solution "solution" exists

        # Why only an admin can delete a user
        And I am logged in as 'admin@test.de'
        When I delete User "student1@test.de"

        Then No User with Username "student1@test.de" does exist
        And No Exercise created by User "student1@test.de" should exist
        And No Video created by User "student1@test.de" should exist
        And No CutVideo of original "video1" exists
        And The Solution with ID "solution" does not exist
        And No CourseRole of User "student1@test.de" exists

    Scenario: Removing a Student that is not the only member of a team should move creator to the other member
        Given The User "dozent@test.de" has CourseRole "DOZENT" in Course "course1"
        And The User "student1@test.de" has CourseRole "STUDENT" in Course "course1"
        And The User "student2@test.de" has CourseRole "STUDENT" in Course "course1"
        And An Exercise with ID "exerciseByDozent" created by User "dozent@test.de" in Course "course1" exists
        And A Video with Id "video" created by User "dozent@test.de" exists

        And I am logged in as "dozent@test.de"
        And I have an exercise phase "exercisePhase" belonging to exercise "exerciseByDozent"

        And An ExercisePhaseTeam "team" belonging to exercise phase "exercisePhase" created by "student1@test.de" exists
        And The User "student2@test.de" is member of ExercisePhaseTeam "team"
        And The ExercisePhaseTeam "team" has a Solution "solution"
        And A CutVideo with Id "cutVideo" of Video "video" belonging to Solution "solution" exists
        And The User "student1@test.de" has created an AutosavedSolution "autosavedSolution1" for ExercisePhaseTeam "team"
        And The User "student2@test.de" has created an AutosavedSolution "autosavedSolution2" for ExercisePhaseTeam "team"

        And I am logged in as "admin@test.de"
        When I delete User "student1@test.de"

        Then No User with Username "student1@test.de" does exist
        And No AutosavedSolution of User "student1@test.de" does exist
        And The ExercisePhaseTeam "team" exists
        And The creator of ExercisePhaseTeam "team" should be "student2@test.de"
        And The Solution "solution" exists
        And The CutVideo with Id "cutVideo" exists
        And No CourseRole of User "student1@test.de" exists

    Scenario: Removing a Student that is the only member of a team should anonymize the team creator
        Given The User "dozent@test.de" has CourseRole "DOZENT" in Course "course1"
        And The User "student1@test.de" has CourseRole "STUDENT" in Course "course1"
        And The User "student2@test.de" has CourseRole "STUDENT" in Course "course1"
        And An Exercise with ID "exerciseByDozent" created by User "dozent@test.de" in Course "course1" exists

        And I am logged in as "dozent@test.de"
        And I have an exercise phase "exercisePhase1" belonging to exercise "exerciseByDozent"
        And I have an exercise phase "exercisePhase2" belonging to exercise "exerciseByDozent"

        And An ExercisePhaseTeam "team1" belonging to exercise phase "exercisePhase1" created by "student1@test.de" exists
        And The User "student2@test.de" is member of ExercisePhaseTeam "team1"
        And The ExercisePhaseTeam "team1" has a Solution "solution1"
        And The User "student1@test.de" has created an AutosavedSolution "autosavedSolution1" for ExercisePhaseTeam "team1"
        And The User "student2@test.de" has created an AutosavedSolution "autosavedSolution2" for ExercisePhaseTeam "team1"

        And An ExercisePhaseTeam "team2" belonging to exercise phase "exercisePhase2" created by "student1@test.de" exists
        And The ExercisePhaseTeam "team2" has a Solution "solution2"
        And The User "student1@test.de" has created an AutosavedSolution "autosavedSolution3" for ExercisePhaseTeam "team2"

        And I am logged in as "admin@test.de"
        When I delete User "student1@test.de"

        Then The User with Id "student1@test.de" should be anonymized
        And No AutosavedSolution of User "student1@test.de" does exist
        And The ExercisePhaseTeam "team1" exists
        And The ExercisePhaseTeam "team2" exists
        And The creator of ExercisePhaseTeam "team1" should be "student2@test.de"
        And The creator of ExercisePhaseTeam "team2" should not be "student1@test.de"
        And The Solution "solution1" exists
        And The Solution "solution2" exists
        And No CourseRole of User "student1@test.de" exists

    Scenario: Remove Admin
        Given A User "admin2@test.de" with the role "ROLE_ADMIN" exists
        And I am logged in as "admin@test.de"
        When I delete User "admin2@test.de"
        Then No User with Username "admin2@test.de" does exist

    Scenario: Remove Dozent in a course with no other Dozent
        Given The User "dozent@test.de" has CourseRole "DOZENT" in Course "course1"
        And An Exercise with ID "exerciseByDozent1" created by User "dozent@test.de" in Course "course1" exists

        And I am logged in as "dozent@test.de"
        And I have an exercise phase "exercisePhase1" belonging to exercise "exerciseByDozent1"
        And A Video with Id "video1" created by User "dozent@test.de" exists
        And the Video with Id "video1" is added to Course "course1"
        And An Attachment with Id "attachment1" created by User "dozent@test.de" exists for ExercisePhase "exercisePhase1"
        And Exercise "exerciseByDozent1" is published

        And I am logged in as "admin@test.de"
        When I delete User "dozent@test.de"

        Then The User with Id "dozent@test.de" does not exist
        And No CourseRole of User "dozent@test.de" exists
        And The Course "course1" does not exist
        And The Exercise "exerciseByDozent1" does not exist
        And The ExercisePhase "exercisePhase1" does not exist
        And The Video "video1" does not exist
        And The Attachment "attachment1" does not exist

    Scenario: Remove Dozent in a course with another Dozent
        Given The User "dozent@test.de" has CourseRole "DOZENT" in Course "course1"
        And A User "dozent2@test.de" with the role "ROLE_DOZENT" exists
        And The User "dozent2@test.de" has CourseRole "DOZENT" in Course "course1"
        And An Exercise with ID "exerciseByDozent1" created by User "dozent@test.de" in Course "course1" exists

        And I am logged in as "dozent@test.de"
        And I have an exercise phase "exercisePhase1" belonging to exercise "exerciseByDozent1"
        And A Video with Id "video1" created by User "dozent@test.de" exists
        And the Video with Id "video1" is added to Course "course1"
        And An Attachment with Id "attachment1" created by User "dozent@test.de" exists for ExercisePhase "exercisePhase1"
        And Exercise "exerciseByDozent1" is published

        And I am logged in as "admin@test.de"
        When I delete User "dozent@test.de"

        Then The User with Id "dozent@test.de" does not exist
        And No CourseRole of User "dozent@test.de" exists
        And The Course "course1" does exist
        And The Exercise "exerciseByDozent1" exists
        And The ExercisePhase "exercisePhase1" exists
        And The Video "video1" does not exist
        And The Attachment "attachment1" exists
