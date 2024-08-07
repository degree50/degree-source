@fixtures @conversionForAPI @integration
Feature: Solution from Model is converted to normalized APISolution by SolutionService

    Background:
        Given I am logged in as "foo@bar.de"
        And I have a course with ID "c"
        And I have an exercise with ID "ex" belonging to course "c"
        And I have an exercise phase "ex-p1" belonging to exercise "ex"
        And I have a team with ID "team-1" belonging to exercise phase "ex-p1"
        And I have a predefined videoCodePrototype belonging to exercise phase "ex-p1" and with properties
            | id      | name | color   |
            | foo_bar | Foo  | #ffffff |

    Scenario: Conversion for general purpose (e.g. exercisePhase/show, exercisePhase/update-solution, exercisePhase/update-currentEditor)
        Given A Video with Id "video-1" created by User "foo@bar.de" exists
        Given I have a solution with ID "solution-1" belonging to team with ID "team-1" with solutionData as JSON
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
                  "color": "#ff9300",
                  "userCreated": false,
                  "videoCodes": [
                    {
                      "id": "1605544765106_#ff9300",
                      "name": "Lob; Kompliment",
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
        Given A CutVideo with Id "cut-video-1" of Video "video-1" belonging to Solution "solution-1" exists
        When I convert the persisted serverSideSolution for team "team-1" to the clientSideSolution
        Then I get normalized client side data as JSON
        """
        {
          "solutions": {
            "byId": {
              "solution-1": {
                "solutionData": {
                  "annotations": ["solution-1_0"],
                  "videoCodes": ["solution-1_0", "solution-1_1"],
                  "cutList": ["solution-1_0"],
                  "videoCodePrototypes": [
                    "a9fabe25-081e-47bd-8905-4bd46ed3cadf",
                    "1605544765106_#ff9300",
                    "foo_bar"
                  ],
                  "material": null
                },
                "id": "solution-1",
                "userName": "foo@bar.de",
                "userId": "foo@bar.de",
                "cutVideo": {
                  "id": "cut-video-1",
                  "name": "Video-Schnitt von c > ex > ex-p1",
                  "createdAt": "01.04.2024",
                  "duration": 0,
                  "url": {
                    "hls": "",
                    "mp4": "",
                    "vtt": "",
                    "thumbnail": ""
                  }
                },
                "fromGroupPhase": false,
                "status": "IN_BEARBEITUNG"
              }
            },
            "current": "solution-1",
            "previous": []
          },
          "annotations": {
            "byId": {
              "solution-1_0": {
                "id": "solution-1_0",
                "start": "00:01:03.315",
                "end": "00:01:30.000",
                "text": "Annotation 1",
                "memo": "",
                "color": null,
                "solutionId": "solution-1"
              }
            }
          },
          "videoCodes": {
            "byId": {
              "solution-1_0": {
                "id": "solution-1_0",
                "start": "00:02:18.453",
                "end": "00:02:45.000",
                "text": "VideoCode 1",
                "memo": "Ein Memo",
                "color": "#ff9300",
                "idFromPrototype": "1605544765106_#ff9300",
                "solutionId": "solution-1"
              },
              "solution-1_1": {
                "id": "solution-1_1",
                "start": "00:00:01.000",
                "end": "00:00:04.000",
                "text": "VideoCode 2",
                "memo": "",
                "color": "#ffffff",
                "idFromPrototype": "foo_bar",
                "solutionId": "solution-1"
              }
            }
          },
          "cuts": {
            "byId": {
              "solution-1_0": {
                "id": "solution-1_0",
                "start": "00:01:03.315",
                "end": "00:01:30.000",
                "text": "Cut 1",
                "memo": "",
                "color": null,
                "solutionId": "solution-1",
                "offset": 0,
                "playbackRate": 1
              }
            }
          },
          "videoCodePrototypes": {
            "byId": {
              "a9fabe25-081e-47bd-8905-4bd46ed3cadf": {
                "id": "a9fabe25-081e-47bd-8905-4bd46ed3cadf",
                "name": "Verschiedene Arten der Beteiligung und der Motivation",
                "color": "#ff9300",
                "videoCodes": [
                  {
                    "id": "1605544765106_#ff9300",
                    "name": "Lob; Kompliment",
                    "color": "#ff9300",
                    "videoCodes": [],
                    "parentId": null,
                    "userCreated": true
                  }
                ],
                "parentId": null,
                "userCreated": false
              },
              "1605544765106_#ff9300": {
                "id": "1605544765106_#ff9300",
                "name": "Lob; Kompliment",
                "color": "#ff9300",
                "videoCodes": [],
                "parentId": null,
                "userCreated": true
              },
              "foo_bar": {
                "id": "foo_bar",
                "name": "Foo",
                "color": "#ffffff",
                "videoCodes": [],
                "parentId": null,
                "userCreated": false
              }
            }
          },
          "materials": {
            "byId": []
          }
        }
        """

    Scenario: Handle solution which depends on previous phase
        Given I have a solution with ID "solution-1" belonging to team with ID "team-1" with solutionData as JSON
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
                  "color": "#ff9300",
                  "userCreated": false,
                  "videoCodes": [
                    {
                      "id": "1605544765106_#ff9300",
                      "name": "Lob; Kompliment",
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
        Given I have an exercise phase "ex-p2" belonging to exercise "ex"
        Given The exercise phase "ex-p1" depends on the previous phase "ex-p2"
        Given I have a team with ID "team-2" belonging to exercise phase "ex-p2"
        Given I am a member of "team-1"
        Given I am a member of "team-2"
        Given I have a solution with ID "previous-solution-1" belonging to team with ID "team-2" with solutionData as JSON
        """
            {
              "annotations": [
                {
                  "start": "00:01:03.315",
                  "end": "00:01:30.000",
                  "text": "Previous Annotation 1",
                  "memo": "Popel",
                  "color": null
                }
              ],
              "videoCodes": [],
              "customVideoCodesPool": [],
              "cutList": []
          }
        """
        When I convert the persisted serverSideSolution for team "team-1" to the clientSideSolution
        Then I get normalized client side data as JSON
        """
        {
          "solutions": {
            "byId": {
              "solution-1": {
                "solutionData": {
                  "annotations": ["solution-1_0"],
                  "videoCodes": ["solution-1_0", "solution-1_1"],
                  "cutList": ["solution-1_0"],
                  "videoCodePrototypes": [
                    "a9fabe25-081e-47bd-8905-4bd46ed3cadf",
                    "1605544765106_#ff9300",
                    "foo_bar"
                  ],
                  "material": null
                },
                "id": "solution-1",
                "userName": "foo@bar.de",
                "userId": "foo@bar.de",
                "cutVideo": null,
                "fromGroupPhase": false,
                "status": "IN_BEARBEITUNG"
              },
              "previous-solution-1": {
                "solutionData": {
                  "annotations": ["previous-solution-1_0"],
                  "videoCodes": [],
                  "cutList": [],
                  "videoCodePrototypes": [],
                  "material": null
                },
                "id": "previous-solution-1",
                "userName": "foo@bar.de",
                "userId": "foo@bar.de",
                "cutVideo": null,
                "fromGroupPhase": false,
                "status": "IN_BEARBEITUNG"
              }
            },
            "current": "solution-1",
            "previous": ["previous-solution-1"]
          },
          "annotations": {
            "byId": {
              "solution-1_0": {
                "id": "solution-1_0",
                "start": "00:01:03.315",
                "end": "00:01:30.000",
                "text": "Annotation 1",
                "memo": "",
                "color": null,
                "solutionId": "solution-1"
              },
              "previous-solution-1_0": {
                "id": "previous-solution-1_0",
                "start": "00:01:03.315",
                "end": "00:01:30.000",
                "text": "Previous Annotation 1",
                "memo": "Popel",
                "color": null,
                "solutionId": "previous-solution-1"
              }
            }
          },
          "videoCodes": {
            "byId": {
              "solution-1_0": {
                "id": "solution-1_0",
                "start": "00:02:18.453",
                "end": "00:02:45.000",
                "text": "VideoCode 1",
                "memo": "Ein Memo",
                "color": "#ff9300",
                "idFromPrototype": "1605544765106_#ff9300",
                "solutionId": "solution-1"
              },
              "solution-1_1": {
                "id": "solution-1_1",
                "start": "00:00:01.000",
                "end": "00:00:04.000",
                "text": "VideoCode 2",
                "memo": "",
                "color": "#ffffff",
                "idFromPrototype": "foo_bar",
                "solutionId": "solution-1"
              }
            }
          },
          "cuts": {
            "byId": {
              "solution-1_0": {
                "id": "solution-1_0",
                "start": "00:01:03.315",
                "end": "00:01:30.000",
                "text": "Cut 1",
                "memo": "",
                "color": null,
                "solutionId": "solution-1",
                "offset": 0,
                "playbackRate": 1
              }
            }
          },
          "videoCodePrototypes": {
            "byId": {
              "a9fabe25-081e-47bd-8905-4bd46ed3cadf": {
                "id": "a9fabe25-081e-47bd-8905-4bd46ed3cadf",
                "name": "Verschiedene Arten der Beteiligung und der Motivation",
                "color": "#ff9300",
                "videoCodes": [
                  {
                    "id": "1605544765106_#ff9300",
                    "name": "Lob; Kompliment",
                    "color": "#ff9300",
                    "videoCodes": [],
                    "parentId": null,
                    "userCreated": true
                  }
                ],
                "parentId": null,
                "userCreated": false
              },
              "1605544765106_#ff9300": {
                "id": "1605544765106_#ff9300",
                "name": "Lob; Kompliment",
                "color": "#ff9300",
                "videoCodes": [],
                "parentId": null,
                "userCreated": true
              },
              "foo_bar": {
                "id": "foo_bar",
                "name": "Foo",
                "color": "#ffffff",
                "videoCodes": [],
                "parentId": null,
                "userCreated": false
              }
            }
          },
          "materials": {
            "byId": []
          }
        }
        """

    Scenario: Conversion uses last autosavedSolution
        Given I have an empty solution with ID "solution-1" belonging to team "team-1"
        And I have an auto saved solution with ID "auto-saved-solution-1" belonging to team "team-1" with solutionData as JSON
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
                  "color": "#ff9300",
                  "userCreated": false,
                  "videoCodes": [
                    {
                      "id": "1605544765106_#ff9300",
                      "name": "Lob; Kompliment",
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
        When I convert the persisted serverSideSolution for team "team-1" to the clientSideSolution
        Then I get normalized client side data as JSON
        """
                {
          "solutions": {
            "byId": {
              "solution-1": {
                "solutionData": {
                  "annotations": ["solution-1_0"],
                  "videoCodes": ["solution-1_0", "solution-1_1"],
                  "cutList": ["solution-1_0"],
                  "videoCodePrototypes": [
                    "a9fabe25-081e-47bd-8905-4bd46ed3cadf",
                    "1605544765106_#ff9300",
                    "foo_bar"
                  ],
                  "material": null
                },
                "id": "solution-1",
                "userName": "foo@bar.de",
                "userId": "foo@bar.de",
                "cutVideo": null,
                "fromGroupPhase": false,
                "status": "IN_BEARBEITUNG"
              }
            },
            "current": "solution-1",
            "previous": []
          },
          "annotations": {
            "byId": {
              "solution-1_0": {
                "id": "solution-1_0",
                "start": "00:01:03.315",
                "end": "00:01:30.000",
                "text": "Annotation 1",
                "memo": "",
                "color": null,
                "solutionId": "solution-1"
              }
            }
          },
          "videoCodes": {
            "byId": {
              "solution-1_0": {
                "id": "solution-1_0",
                "start": "00:02:18.453",
                "end": "00:02:45.000",
                "text": "VideoCode 1",
                "memo": "Ein Memo",
                "color": "#ff9300",
                "idFromPrototype": "1605544765106_#ff9300",
                "solutionId": "solution-1"
              },
              "solution-1_1": {
                "id": "solution-1_1",
                "start": "00:00:01.000",
                "end": "00:00:04.000",
                "text": "VideoCode 2",
                "memo": "",
                "color": "#ffffff",
                "idFromPrototype": "foo_bar",
                "solutionId": "solution-1"
              }
            }
          },
          "cuts": {
            "byId": {
              "solution-1_0": {
                "id": "solution-1_0",
                "start": "00:01:03.315",
                "end": "00:01:30.000",
                "text": "Cut 1",
                "memo": "",
                "color": null,
                "solutionId": "solution-1",
                "offset": 0,
                "playbackRate": 1
              }
            }
          },
          "videoCodePrototypes": {
            "byId": {
              "a9fabe25-081e-47bd-8905-4bd46ed3cadf": {
                "id": "a9fabe25-081e-47bd-8905-4bd46ed3cadf",
                "name": "Verschiedene Arten der Beteiligung und der Motivation",
                "color": "#ff9300",
                "videoCodes": [
                  {
                    "id": "1605544765106_#ff9300",
                    "name": "Lob; Kompliment",
                    "color": "#ff9300",
                    "videoCodes": [],
                    "parentId": null,
                    "userCreated": true
                  }
                ],
                "parentId": null,
                "userCreated": false
              },
              "1605544765106_#ff9300": {
                "id": "1605544765106_#ff9300",
                "name": "Lob; Kompliment",
                "color": "#ff9300",
                "videoCodes": [],
                "parentId": null,
                "userCreated": true
              },
              "foo_bar": {
                "id": "foo_bar",
                "name": "Foo",
                "color": "#ffffff",
                "videoCodes": [],
                "parentId": null,
                "userCreated": false
              }
            }
          },
          "materials": {
            "byId": []
          }
        }
        """

    Scenario: Conversion for multiple teams (e.g. for the solution view exercisePhase/show-solutions)
        Given I have a solution with ID "solution-1" belonging to team with ID "team-1" with solutionData as JSON
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
                  "color": "#ff9300",
                  "userCreated": false,
                  "videoCodes": [
                    {
                      "id": "1605544765106_#ff9300",
                      "name": "Lob; Kompliment",
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
        Given I have a team with ID "team-2" belonging to exercise phase "ex-p1"
        And I have a solution with ID "solution-2" belonging to team with ID "team-2" with solutionData as JSON
            """
            {
              "annotations": [
                {
                  "start": "00:01:03.315",
                  "end": "00:01:30.000",
                  "text": "Annotation 1 Of Team 2",
                  "memo": "",
                  "color": null
                }
              ],
              "videoCodes": [],
              "customVideoCodesPool": [
                {
                  "id": "a9fabe25-081e-47bd-8905-4bd46ed3cadf",
                  "name": "Verschiedene Arten der Beteiligung und der Motivation",
                  "color": "#ff9300",
                  "userCreated": false,
                  "videoCodes": [
                    {
                      "id": "1605544765106_#ff9300",
                      "name": "Lob; Kompliment",
                      "color": "#ff9300",
                      "userCreated": true,
                      "videoCodes": []
                    }
                  ]
                }
              ],
              "cutList": [ ]
            }
            """
        Given I have a team with ID "team-3" belonging to exercise phase "ex-p1"
        And I have a solution with ID "solution-3" belonging to team with ID "team-3" with solutionData as JSON
            """
            {
              "annotations": [],
              "videoCodes": [
                {
                  "start": "00:02:18.453",
                  "end": "00:02:45.000",
                  "text": "VideoCode 1 Of Team 3",
                  "memo": "Ein Memo",
                  "color": "#ff9300",
                  "idFromPrototype": "1605544765106_#ff9300"
                }
              ],
              "customVideoCodesPool": [
                {
                  "id": "a9fabe25-081e-47bd-8905-4bd46ed3cadf",
                  "name": "Verschiedene Arten der Beteiligung und der Motivation",
                  "color": "#ff9300",
                  "userCreated": false,
                  "videoCodes": [
                    {
                      "id": "1605544765106_#ff9300",
                      "name": "Lob; Kompliment",
                      "color": "#ff9300",
                      "userCreated": true,
                      "videoCodes": []
                    }
                  ]
                }
              ],
              "cutList": []
            }
            """
        When I convert the persisted serverSideSolutions for all teams of exercise phase "ex-p1" to the client side data
        Then I get normalized client side data as JSON
        """
        {
          "solutions": {
            "byId": {
              "solution-1": {
                "solutionData": {
                  "annotations": ["solution-1_0"],
                  "videoCodes": ["solution-1_0", "solution-1_1"],
                  "cutList": ["solution-1_0"],
                  "videoCodePrototypes": [
                    "a9fabe25-081e-47bd-8905-4bd46ed3cadf",
                    "1605544765106_#ff9300"
                  ],
                  "material": null
                },
                "id": "solution-1",
                "userName": "foo@bar.de",
                "userId": "foo@bar.de",
                "cutVideo": null,
                "fromGroupPhase": false,
                "status": "IN_BEARBEITUNG"
              },
              "solution-2": {
                "solutionData": {
                  "annotations": ["solution-2_0"],
                  "videoCodes": [],
                  "cutList": [],
                  "videoCodePrototypes": [
                    "a9fabe25-081e-47bd-8905-4bd46ed3cadf",
                    "1605544765106_#ff9300"
                  ],
                  "material": null
                },
                "id": "solution-2",
                "userName": "foo@bar.de",
                "userId": "foo@bar.de",
                "cutVideo": null,
                "fromGroupPhase": false,
                "status": "IN_BEARBEITUNG"
              },
              "solution-3": {
                "solutionData": {
                  "annotations": [],
                  "videoCodes": ["solution-3_0"],
                  "cutList": [],
                  "videoCodePrototypes": [
                    "a9fabe25-081e-47bd-8905-4bd46ed3cadf",
                    "1605544765106_#ff9300"
                  ],
                  "material": null
                },
                "id": "solution-3",
                "userName": "foo@bar.de",
                "userId": "foo@bar.de",
                "cutVideo": null,
                "fromGroupPhase": false,
                "status": "IN_BEARBEITUNG"
              }
            },
            "current": null,
            "previous": ["solution-1", "solution-2", "solution-3"]
          },
          "annotations": {
            "byId": {
              "solution-1_0": {
                "id": "solution-1_0",
                "start": "00:01:03.315",
                "end": "00:01:30.000",
                "text": "Annotation 1",
                "memo": "",
                "color": null,
                "solutionId": "solution-1"
              },
              "solution-2_0": {
                "id": "solution-2_0",
                "start": "00:01:03.315",
                "end": "00:01:30.000",
                "text": "Annotation 1 Of Team 2",
                "memo": "",
                "color": null,
                "solutionId": "solution-2"
              }
            }
          },
          "videoCodes": {
            "byId": {
              "solution-1_0": {
                "id": "solution-1_0",
                "start": "00:02:18.453",
                "end": "00:02:45.000",
                "text": "VideoCode 1",
                "memo": "Ein Memo",
                "color": "#ff9300",
                "idFromPrototype": "1605544765106_#ff9300",
                "solutionId": "solution-1"
              },
              "solution-1_1": {
                "id": "solution-1_1",
                "start": "00:00:01.000",
                "end": "00:00:04.000",
                "text": "VideoCode 2",
                "memo": "",
                "color": "#ffffff",
                "idFromPrototype": "foo_bar",
                "solutionId": "solution-1"
              },
              "solution-3_0": {
                "id": "solution-3_0",
                "start": "00:02:18.453",
                "end": "00:02:45.000",
                "text": "VideoCode 1 Of Team 3",
                "memo": "Ein Memo",
                "color": "#ff9300",
                "idFromPrototype": "1605544765106_#ff9300",
                "solutionId": "solution-3"
              }
            }
          },
          "cuts": {
            "byId": {
              "solution-1_0": {
                "id": "solution-1_0",
                "start": "00:01:03.315",
                "end": "00:01:30.000",
                "text": "Cut 1",
                "memo": "",
                "color": null,
                "solutionId": "solution-1",
                "offset": 0,
                "playbackRate": 1
              }
            }
          },
          "videoCodePrototypes": {
            "byId": {
              "a9fabe25-081e-47bd-8905-4bd46ed3cadf": {
                "id": "a9fabe25-081e-47bd-8905-4bd46ed3cadf",
                "name": "Verschiedene Arten der Beteiligung und der Motivation",
                "color": "#ff9300",
                "videoCodes": [
                  {
                    "id": "1605544765106_#ff9300",
                    "name": "Lob; Kompliment",
                    "color": "#ff9300",
                    "videoCodes": [],
                    "parentId": null,
                    "userCreated": true
                  }
                ],
                "parentId": null,
                "userCreated": false
              },
              "1605544765106_#ff9300": {
                "id": "1605544765106_#ff9300",
                "name": "Lob; Kompliment",
                "color": "#ff9300",
                "videoCodes": [],
                "parentId": null,
                "userCreated": true
              },
              "foo_bar": {
                "id": "foo_bar",
                "name": "Foo",
                "color": "#ffffff",
                "videoCodes": [],
                "parentId": null,
                "userCreated": false
              }
            }
          },
          "materials": {
            "byId": []
          }
        }
        """
