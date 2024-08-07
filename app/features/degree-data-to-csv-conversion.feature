@fixtures @conversionToCSV @integration
Feature: Degree data (courses, solutions etc.) is converted into csv data

    Background:

    Scenario: Conversion of all degree data for a single course
        Given I am logged in as "foo@bar.de"
        # Implies that the user also has the "DOZENT" role for this course
        Given I have a course with ID "course-1"
        Given I have an exercise with ID "exercise-1" belonging to course "course-1"
        Given I have an exercise phase "ex-p1" belonging to exercise "exercise-1"
        Given I have a team with ID "team-1" belonging to exercise phase "ex-p1"
        Given A user "testuser@bar.de" exists
        Given User "testuser@bar.de" belongs to "team-1"
        Given A Video with Id "video-1" created by User "foo@bar.de" exists
        Given I have a predefined videoCodePrototype belonging to exercise phase "ex-p1" and with properties
            | id      | name | color   |
            | foo_bar | Foo  | #ffffff |
        Given I have a solution with ID "solution-1" belonging to team with ID "team-1" with solutionData as JSON
            """
            {
              "annotations": [
                {
                  "start": "00:01:03.315",
                  "end": "00:01:30.000",
                  "text": "Annotation 1 - and now comes a line break \n and here is a carriage return \r",
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
        Given A CutVideo with Id "cut-video-1" of Video "video-1" belonging to Solution "solution-1" exists
        When I convert all data for "course-1" to csv
        Then I have CSVDto-list containing a file "README.md"
        # Note that {{CREATED_AT_DATE}} will be interpolated during the step and be set to the current day.
        # We do this, because the createdAt property of our solution will always be the day the
        # test runs at.
        Then I have a CSVDto-list containing a file "loesungen.csv" with a CSV content string
        """
        loesungsID;kursID;kursName;aufgabenID;aufgabenTitel;aufgabenBeschreibung;erstellungsDatum;status;phasenID;istGruppenphase;phasenTitel;phasenBeschreibung;phasenTyp;bautAufVorherigerPhaseAuf;vorherigePhasenID;teamID;teamErsteller
        solution-1;course-1;course-1;exercise-1;exercise-1;;{{CREATED_AT_DATE}};created;ex-p1;Nein;ex-p1;;videoAnalysis;Nein;-;team-1;foo@bar.de

        """
        Then I have a CSVDto-list containing a file "kurs-mitglieder.csv" with a CSV content string
        """
        kursID;kursName;kursRolle;nutzerName
        course-1;course-1;DOZENT;foo@bar.de

        """
        Then I have a CSVDto-list containing a file "team-mitglieder.csv" with a CSV content string
        """
        nutzerID;nutzerName;teamID;teamErstellerID;loesungsID
        testuser@bar.de;testuser@bar.de;team-1;foo@bar.de;solution-1

        """
        # Note how we check that line breaks/carriage returns are being replaced correctly during this step!
        Then I have a CSVDto-list containing a file "annotationen.csv" with a CSV content string
        """
        loesungsID;start;end;text;memo;farbe
        solution-1;00:01:03.315;00:01:30.000;"Annotation 1 - and now comes a line break   and here is a carriage return  ";;

        """
        Then I have a CSVDto-list containing a file "video-kodierungen.csv" with a CSV content string
        """
        loesungsID;start;end;text;memo;farbe;codeID;codeName;codeFarbe;elternCodeID;selbstErstellterCode
        solution-1;00:02:18.453;00:02:45.000;"VideoCode 1";"Ein Memo";#ff9300;1605544765106_#ff9300;"Lob; Kompliment";#ff9300;;ja
        solution-1;00:00:01.000;00:00:04.000;"VideoCode 2";;#ffffff;foo_bar;;;;

        """
        Then I have a CSVDto-list containing a file "schnitte.csv" with a CSV content string
        """
        loesungsID;start;end;text;memo;farbe
        solution-1;00:01:03.315;00:01:30.000;"Cut 1";;

        """

    Scenario: Check if previousExercisePhaseID is retrieved correctly
        Given I am logged in as "foo@bar.de"
        # Implies that the user also has the "DOZENT" role for this course
        Given I have a course with ID "course-1"
        Given I have an exercise with ID "exercise-1" belonging to course "course-1"
        Given I have an exercise phase "ex-p1" belonging to exercise "exercise-1"
        Given I have an exercise phase "ex-p2" belonging to exercise "exercise-1"
        Given I have a team with ID "team-1" belonging to exercise phase "ex-p1"
        Given I have a team with ID "team-2" belonging to exercise phase "ex-p2"
        Given The exercise phase "ex-p2" depends on the previous phase "ex-p1"
        Given A user "testuser@bar.de" exists
        Given User "testuser@bar.de" belongs to "team-1"
        Given User "testuser@bar.de" belongs to "team-2"
        Given A Video with Id "video-1" created by User "foo@bar.de" exists
        Given I have a predefined videoCodePrototype belonging to exercise phase "ex-p1" and with properties
            | id      | name | color   |
            | foo_bar | Foo  | #ffffff |
        Given I have a solution with ID "solution-1" belonging to team with ID "team-1" with solutionData as JSON
            """
            {
              "annotations": [
                {
                  "start": "00:01:03.315",
                  "end": "00:01:30.000",
                  "text": "Annotation 1 - and now comes a line break \n and here is a carriage return \r",
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
        Given A CutVideo with Id "cut-video-1" of Video "video-1" belonging to Solution "solution-1" exists
        Given I have a solution with ID "solution-2" belonging to team with ID "team-2" with solutionData as JSON
            """
            {
              "annotations": [ ],
              "videoCodes": [ ],
              "customVideoCodesPool": [ ],
              "cutList": [
                  {
                      "start": "00:00:03.315",
                      "end": "00:00:30.000",
                      "text": "Awesome cut",
                      "memo": "Some memo",
                      "color": null,
                      "offset": 0,
                      "playbackRate": "1"
                  }
              ]
            }
            """
        When I convert all data for "course-1" to csv
        # Note that {{CREATED_AT_DATE}} will be interpolated during the step and be set to the current day.
        # We do this, because the createdAt property of our solution will always be the day the
        # test runs at.
        Then I have a CSVDto-list containing a file "loesungen.csv" with a CSV content string
        """
        loesungsID;kursID;kursName;aufgabenID;aufgabenTitel;aufgabenBeschreibung;erstellungsDatum;status;phasenID;istGruppenphase;phasenTitel;phasenBeschreibung;phasenTyp;bautAufVorherigerPhaseAuf;vorherigePhasenID;teamID;teamErsteller
        solution-1;course-1;course-1;exercise-1;exercise-1;;{{CREATED_AT_DATE}};created;ex-p1;Nein;ex-p1;;videoAnalysis;Nein;-;team-1;foo@bar.de
        solution-2;course-1;course-1;exercise-1;exercise-1;;{{CREATED_AT_DATE}};created;ex-p2;Nein;ex-p2;;videoAnalysis;Ja;ex-p1;team-2;foo@bar.de

        """
