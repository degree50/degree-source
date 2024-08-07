@fixtures @integration
Feature: CRUD operations on the videoRepository

    Background:
        Given I am logged in as "foo@bar.de"
        And I have a course with ID "c"
        And I have an exercise with ID "ex" belonging to course "c"
        And I have an exercise with ID "ex-2" belonging to course "c"
        And I have an exercise phase "ex-p1" belonging to exercise "ex"
        And I have an exercise phase "ex-p2" belonging to exercise "ex"
        And I have an exercise phase "ex-p3" belonging to exercise "ex-2"
        And I have a team with ID "team-1" belonging to exercise phase "ex-p1"
        And A Video with Id "video-1" created by User "foo@bar.de" exists
        And I have a video "video-1" belonging to exercise phase "ex-p1"
        And I have a solution with ID "solution-1" belonging to team with ID "team-1" with solutionData as JSON
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
              "videoCodes": [
                {
                  "start": "00:02:18.453",
                  "end": "00:02:45.000",
                  "text": "VideoCode 1",
                  "memo": "Ein Memo",
                  "color": "#ff9300",
                  "idFromPrototype": "1605544765106_#ff9300"
                },
                {
                  "start": "00:00:01.000",
                  "end": "00:00:04.000",
                  "text": "VideoCode 2",
                  "memo": "",
                  "color": "#ffffff",
                  "idFromPrototype": "foo_bar"
                }
              ],
              "customVideoCodesPool": [
                {
                  "id": "a9fabe25-081e-47bd-8905-4bd46ed3cadf",
                  "name": "Verschiedene Arten der Beteiligung und der Motivation",
                  "description": "",
                  "color": "#ff9300",
                  "userCreated": false,
                  "videoCodes": [
                    {
                      "id": "1605544765106_#ff9300",
                      "name": "Lob; Kompliment",
                      "description": "",
                      "color": "#ff9300",
                      "userCreated": true,
                      "videoCodes": []
                    }
                  ]
                }
              ],
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
        And A CutVideo with Id "cutVideo" of Video "video-1" belonging to Solution "solution-1" exists

    Scenario: Find videos by creator and not cut videos
        Then I only receive the regular video "video-1" and not the cut video "cut-video-1" for creator "foo@bar.de"

    Scenario: When a Video is deleted, it is removed from Phases and all cut videos are deleted as well
        When I delete the video "video-1"
        Then The video "video-1" is deleted
        And The ExercisePhase "ex-p1" exists
        And The ExercisePhase "ex-p2" exists
        And The Exercise "ex" exists
        And The ExercisePhaseTeam "team-1" exists
        And The Solution "solution-1" exists
        And The CutVideo with Id "cutVideo" does not exist
        And The Exercise "ex-2" exists
        And 3 exercise phases should exist
