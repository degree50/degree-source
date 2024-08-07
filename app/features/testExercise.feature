@fixtures @playwright
Feature: Test an exercise

    Background:
        Given A User "test-dozent@sandstorm.de" with the role "ROLE_DOZENT" exists
        And A User "test-student@sandstorm.de" with the role "ROLE_STUDENT" exists
        And A Course with ID "course1" exists
        And The User "test-dozent@sandstorm.de" has CourseRole "DOZENT" in Course "course1"
        And The User "test-student@sandstorm.de" has CourseRole "STUDENT" in Course "course1"
        And An Exercise with ID "c1-exercise-dozent" created by User "test-dozent@sandstorm.de" in Course "course1" exists
        And The exercise "c1-exercise-dozent" has these phases:
            | id          | type          | isGroupPhase |
            | analysis1   | videoAnalysis | no           |
            | cut1        | videoCutting  | no           |
            | groupPhase1 | videoCutting  | yes          |
            | reflexion1  | reflexion     | no           |
            | material1   | material      | no           |
            | material2   | material      | no           |

        And Exercise "c1-exercise-dozent" is published

    Scenario: User can test his own exercise
        Given I am logged in via browser as "test-dozent@sandstorm.de"
        When I visit url "/exercise/c1-exercise-dozent/test"
        Then the page should contain the text "Achtung: Aufgabe wird getestet. Bearbeitung ist für andere nicht sichtbar."
        When I click on "Aufgabe testen"
        Then the page should contain the text "Achtung: Aufgabe wird getestet. Bearbeitung ist für andere nicht sichtbar."
        And the page should contain the text "Phase 1: analysis1"
        When I click on "reflexion1"
        Then the page should contain the text "Achtung: Aufgabe wird getestet. Bearbeitung ist für andere nicht sichtbar."
        And the page should contain the text "Phase 4: reflexion1"
        When I click on "Vorherige Phase"
        Then the page should contain the text "Achtung: Aufgabe wird getestet. Bearbeitung ist für andere nicht sichtbar."
        And the page should contain the text "Phase 3: groupPhase1"
        When I click on "Nächste Phase"
        Then the page should contain the text "Achtung: Aufgabe wird getestet. Bearbeitung ist für andere nicht sichtbar."
        And the page should contain the text "Phase 4: reflexion1"

    Scenario: User can create a test solution and reset it
        Given I am logged in via browser as "test-dozent@sandstorm.de"
        When I visit url "/exercise/test/c1-exercise-dozent/phase/analysis1"
        Then the page should contain the text "Achtung: Aufgabe wird getestet. Bearbeitung ist für andere nicht sichtbar."
        When I click on "Phase starten"
        Then the page should contain the text "Testen von Phase 1: analysis1"
        When I click on "Phase abschließen"
        Then the page should contain the text "Mein Ergebnis"
        When I click on "Test Ergebnis zurücksetzen"
        Then the page should contain the text "Test Ergebnis erfolgreich zurückgesetzt!"
        And the page should contain the text "Phase starten"

    Scenario: User can test and start his own phase
        # log in as dozent and test the first phase
        Given I am logged in via browser as "test-dozent@sandstorm.de"
        When I visit url "/exercise/test/c1-exercise-dozent/phase/analysis1"
        Then the page should contain the text "Achtung: Aufgabe wird getestet. Bearbeitung ist für andere nicht sichtbar."
        When I click on "Phase starten"
        Then the page should contain the text "Testen von Phase 1: analysis1"
        When I click on "Phase abschließen"
        Then the page should contain the text "Mein Ergebnis"
        And the page should contain the text "Achtung: Aufgabe wird getestet. Bearbeitung ist für andere nicht sichtbar."
        # than start the first phase with same user
        When I visit url "/exercise/c1-exercise-dozent/phase/analysis1"
        Then the page should not contain the text "Achtung: Aufgabe wird getestet. Bearbeitung ist für andere nicht sichtbar."
        Then the page should contain the text "Phase starten"
        When I click on "Phase starten"
        Then the page should contain the text "Phase 1: analysis1"
        When I click on "Phase abschließen"
        Then the page should contain the text "Mein Ergebnis"
        Then the page should not contain the text "Achtung: Aufgabe wird getestet. Bearbeitung ist für andere nicht sichtbar."

    Scenario: User can start his own phase and test
        # log in as dozent and start the first phase
        Given I am logged in via browser as "test-dozent@sandstorm.de"
        When I visit url "/exercise/c1-exercise-dozent/phase/analysis1"
        Then the page should not contain the text "Achtung: Aufgabe wird getestet. Bearbeitung ist für andere nicht sichtbar."
        Then the page should contain the text "Phase starten"
        When I click on "Phase starten"
        Then the page should contain the text "Phase 1: analysis1"
        When I click on "Phase abschließen"
        Then the page should contain the text "Mein Ergebnis"
        Then the page should not contain the text "Achtung: Aufgabe wird getestet. Bearbeitung ist für andere nicht sichtbar."

        # than test the first phase with same user
        When I visit url "/exercise/test/c1-exercise-dozent/phase/analysis1"
        Then the page should contain the text "Achtung: Aufgabe wird getestet. Bearbeitung ist für andere nicht sichtbar."
        When I click on "Phase starten"
        Then the page should contain the text "Testen von Phase 1: analysis1"
        When I click on "Phase abschließen"
        Then the page should contain the text "Mein Ergebnis"
        And the page should contain the text "Achtung: Aufgabe wird getestet. Bearbeitung ist für andere nicht sichtbar."

    Scenario: The student does not see the test solution from the dozent
        # log in as dozent and test the first phase
        Given I am logged in via browser as "test-dozent@sandstorm.de"
        When I visit url "/exercise/test/c1-exercise-dozent/phase/analysis1"
        Then the page should contain the text "Achtung: Aufgabe wird getestet. Bearbeitung ist für andere nicht sichtbar."
        When I click on "Phase starten"
        Then the page should contain the text "Testen von Phase 1: analysis1"
        When I click on "Phase abschließen"
        Then the page should contain the text "Mein Ergebnis"
        # log in as student and visit the first phase
        Given I am logged in via browser as "test-student@sandstorm.de"
        When I visit url "/exercise/c1-exercise-dozent/phase/analysis1"
        Then the page should contain the text "Keine vorhanden"
