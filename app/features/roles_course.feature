@fixtures @playwright
Feature: Roles and constraints regarding viewing, creating, editing and deletion of Courses

    # role constrains on course entity
    #
    # Note: "Verwalten" is a combination of edit, delete, export CSV, and "Mitglieder Verwalten"
    #
    # admin can
    #   * create courses
    #   * view and "Verwalten" all courses
    # dozent can
    #   * create courses
    #   * view and "Verwalten" courses where he/she is the creator or assigned as KursDozent
    # student can
    #   * view assigned courses
    #
    #   ┌──────────┬────────────────────────────┬────────────────────┬────────────────────────┬────────────────────────┐
    #   │  Action  │        View                │       Create       │  Verwalten             │  Delete                │
    #   ├──────────┼────────────────────────────┼────────────────────┼────────────────────────┼────────────────────────┤
    #   │  admin   │  ALL                       │  YES               │  ALL                   │  ALL                   │
    #   ├──────────┼────────────────────────────┼────────────────────┼────────────────────────┼────────────────────────┤
    #   │  dozent  │  Created | KursDozent      │  YES               │  Created | KursDozent  │  Created | KursDozent  │
    #   ├──────────┼────────────────────────────┼────────────────────┼────────────────────────┤────────────────────────┤
    #   │  student │  KursStudent | KursDozent  │  NO                │  NONE                  │  NONE                  │
    #   └──────────┴────────────────────────────┴────────────────────┴────────────────────────┘────────────────────────┘
    #

    Background:
        Given A User "test-admin@sandstorm.de" with the role "ROLE_ADMIN" exists
        And A User "test-dozent@sandstorm.de" with the role "ROLE_DOZENT" exists
        And A User "test-student@sandstorm.de" with the role "ROLE_STUDENT" exists

        And A Course with ID "course1" exists
        And A Course with ID "course2" exists

        And The User "test-dozent@sandstorm.de" has CourseRole "DOZENT" in Course course1
        And The User "test-student@sandstorm.de" has CourseRole "STUDENT" in Course course1

    #########################
    ### View
    #########################
    Scenario: As admin I can see all courses in exercise overview
        Given I am logged in via browser as "test-admin@sandstorm.de"
        When I visit route "exercise-overview"
        Then the page contains all the following texts:
            | /exercise-overview/course1 |
            | /exercise-overview/course2 |

    Scenario Outline: As admin I can view all course pages
        Given I am logged in via browser as "test-admin@sandstorm.de"
        When I visit url "<url>"
        Then the response status code should be "<statusCode>"
        And the page should contain the text "<text>"

        Examples:
            | url                        | statusCode | text                    |
            | /exercise-overview/course1 | 200        | course1 - Alle Aufgaben |
            | /exercise-overview/course2 | 200        | course2 - Alle Aufgaben |

    Scenario: As dozent I can see my assigned courses in exercise overview
        Given I am logged in via browser as "test-dozent@sandstorm.de"
        When I visit route "exercise-overview"
        Then the page contains all the following texts:
            | /exercise-overview/course1 |
        And the page contains none of the following texts:
            | /exercise-overview/course2 |

    Scenario Outline: As dozent I can view course pages of my assigned courses
        Given I am logged in via browser as "test-dozent@sandstorm.de"
        When I visit url "<url>"
        Then the response status code should be "<statusCode>"
        And the page should contain the text "<text>"

        Examples:
            | url                        | statusCode | text                    |
            | /exercise-overview/course1 | 200        | course1 - Alle Aufgaben |
            | /exercise-overview/course2 | 403        | Zugriff verweigert      |

    Scenario: As student I can see my assigned courses in exercise overview
        Given I am logged in via browser as "test-student@sandstorm.de"
        When I visit route "exercise-overview"
        Then the page contains all the following texts:
            | /exercise-overview/course1 |
        And the page contains none of the following texts:
            | /exercise-overview/course2 |

    Scenario Outline: As student I can view course pages of my assigned courses
        Given I am logged in via browser as "test-student@sandstorm.de"
        When I visit url "<url>"
        Then the response status code should be "<statusCode>"
        And the page should contain the text "<text>"

        Examples:
            | url                        | statusCode | text                    |
            | /exercise-overview/course1 | 200        | course1 - Alle Aufgaben |
            | /exercise-overview/course2 | 403        | Zugriff verweigert      |

    #########################
    ### Create
    #########################
    Scenario: As admin I see the options to create a course
        Given I am logged in via browser as "test-admin@sandstorm.de"
        When I visit route "exercise-overview"
        Then the page contains all the following texts:
            | Kurs anlegen                  |
            | /exercise-overview/course/new |

    Scenario: As admin I can create a course
        Given I am logged in via browser as "test-admin@sandstorm.de"
        When I visit url "/exercise-overview/course/new"
        And I fill out the course form and submit
        Then the response status code should be 200
        And the page should contain the text "Kurs erfolgreich angelegt!"

    Scenario: As dozent I see the options to create a course
        Given I am logged in via browser as "test-dozent@sandstorm.de"
        When I visit route "exercise-overview"
        Then the page contains all the following texts:
            | Kurs anlegen                  |
            | /exercise-overview/course/new |

    Scenario: As dozent I can create a course
        Given I am logged in via browser as "test-dozent@sandstorm.de"
        When I visit url "/exercise-overview/course/new"
        And I fill out the course form and submit
        Then the response status code should be 200
        And the page should contain the text "Kurs erfolgreich angelegt!"

    Scenario: As student I do not see the options to create a course
        Given I am logged in via browser as "test-student@sandstorm.de"
        When I visit route "exercise-overview"
        Then the page contains none of the following texts:
            | Kurs anlegen                  |
            | /exercise-overview/course/new |

    Scenario: As student I can not create a course
        Given I am logged in via browser as "test-student@sandstorm.de"
        When I visit url "/exercise-overview/course/new"
        Then the response status code should be 403
        And the page should contain the text "Zugriff verweigert"

    #########################
    ### Verwalten (edit, delete, export CSV, and "Mitglieder Verwalten")
    #########################
    ## Admin
    Scenario Outline: As admin I can edit "Lernende" for all courses
        Given I am logged in via browser as "test-admin@sandstorm.de"
        When I visit url "/exercise-overview/<courseId>"
        And I click on "Kurs verwalten"
        And I click on "Lernende verwalten"
        And I click on "Lernende speichern"
        Then the response status code should be 200
        And the page should contain the text "Lernende erfolgreich gespeichert!"

        Examples:
            | courseId |
            | course1  |
            | course2  |

    Scenario Outline: As admin I can edit all courses
        Given I am logged in via browser as "test-admin@sandstorm.de"
        When I visit url "/exercise-overview/<courseId>"
        And I click on "Kurs verwalten"
        And I click on "Kurs bearbeiten"
        And I click on "Kurs speichern"
        Then the response status code should be 200
        And the page should contain the text "Kurs erfolgreich gespeichert!"

        Examples:
            | courseId |
            | course1  |
            | course2  |

    Scenario Outline: As admin I can delete all courses
        Given I am logged in via browser as "test-admin@sandstorm.de"
        When I visit url "/exercise-overview/<courseId>"
        And I click on "Kurs verwalten"
        And I click on "Kurs löschen"
        Then the response status code should be 200
        And the page should contain the text "Kurs erfolgreich gelöscht!"

        Examples:
            | courseId |
            | course1  |
            | course2  |

    Scenario: As admin I can not delete a course with exercises in it
        Given An Exercise with ID "exercise1" created by User "test-admin@sandstorm.de" in Course "course1" exists
        And I am logged in via browser as "test-admin@sandstorm.de"
        When I visit url "/exercise-overview/course1"
        And I click on "Kurs verwalten"
        And I click on "Kurs löschen"
        Then the response status code should be 200
        And the page should contain the text "Kurs kann nicht gelöscht werden, so lange Aufgaben zugewiesen sind!"
        And the page should contain the text "course1"

    Scenario Outline: As admin I can download CSV export for all courses
        Given I am logged in via browser as "test-admin@sandstorm.de"
        When I visit url "/exercise-overview/<courseId>"
        And I click on "Kurs verwalten"
        Then I should be able to download the CSV export for "<courseId>" when clicking on "CSV exportieren"

        Examples:
            | courseId |
            | course1  |
            | course2  |

    ## Dozent
    Scenario: As dozent I can edit "Lernende" for assigned courses
        Given I am logged in via browser as "test-dozent@sandstorm.de"
        When I visit url "/exercise-overview/course1"
        And I click on "Kurs verwalten"
        And I click on "Lernende verwalten"
        And I click on "Lernende speichern"
        Then the response status code should be 200
        And the page should contain the text "Lernende erfolgreich gespeichert!"

    Scenario: As dozent I can not edit "Lernende" for unassigned courses
        Given I am logged in via browser as "test-dozent@sandstorm.de"
        # we can't navigate to the course via mouse as it shouldn't even be visible to us
        When I visit url "/exercise-overview/course2/course-members"
        Then the response status code should be 403
        And the page should contain the text "Zugriff verweigert"

    Scenario: As dozent I can edit assigned courses
        Given I am logged in via browser as "test-dozent@sandstorm.de"
        When I visit url "/exercise-overview/course1"
        And I click on "Kurs verwalten"
        And I click on "Kurs bearbeiten"
        And I click on "Kurs speichern"
        Then the response status code should be 200
        And the page should contain the text "Kurs erfolgreich gespeichert!"

    Scenario: As dozent I can not edit unassigned courses
        Given I am logged in via browser as "test-dozent@sandstorm.de"
        # we can't navigate to the course via mouse as it shouldn't even be visible to us
        When I visit url "/exercise-overview/course/edit/course2"
        Then the response status code should be 403
        And the page should contain the text "Zugriff verweigert"

    Scenario: As dozent I can delete assigned courses
        Given I am logged in via browser as "test-dozent@sandstorm.de"
        When I visit url "/exercise-overview/course1"
        And I click on "Kurs verwalten"
        And I click on "Kurs löschen"
        Then the response status code should be 200
        And the page should contain the text "Kurs erfolgreich gelöscht!"

    Scenario: As dozent I can not delete an assigned course with exercises in it
        Given An Exercise with ID "exercise1" created by User "test-admin@sandstorm.de" in Course "course1" exists
        And I am logged in via browser as "test-dozent@sandstorm.de"
        When I visit url "/exercise-overview/course1"
        And I click on "Kurs verwalten"
        And I click on "Kurs löschen"
        Then the response status code should be 200
        And the page should contain the text "Kurs kann nicht gelöscht werden, so lange Aufgaben zugewiesen sind!"
        And the page should contain the text "course1"

    Scenario: As dozent I can not delete unassigned courses
        Given I am logged in via browser as "test-dozent@sandstorm.de"
        # we can't navigate to the course via mouse as it shouldn't even be visible to us
        When I visit url "/exercise-overview/course/delete/course2"
        Then the response status code should be 403
        And the page should contain the text "Zugriff verweigert"

    Scenario: As dozent I can download CSV export for assigned courses
        Given I am logged in via browser as "test-dozent@sandstorm.de"
        When I visit url "/exercise-overview/course1"
        And I click on "Kurs verwalten"
        Then I should be able to download the CSV export for "course1" when clicking on "CSV exportieren"

    Scenario: As dozent I can not download CSV export for unassigned courses
        Given I am logged in via browser as "test-dozent@sandstorm.de"
        # we can't navigate to the course via mouse as it shouldn't even be visible to us
        When I visit url "/exercise-overview/course2/export-csv"
        Then the response status code should be 403
        And the page should contain the text "Zugriff verweigert"

    ## Student
    Scenario: As student I can not see the "Kurs verwalten" options for any courses
        Given I am logged in via browser as "test-student@sandstorm.de"
        When I visit url "/exercise-overview/course1"
        Then the response status code should be 200
        And the page should not contain the text "Kurs verwalten"

    Scenario Outline: As student I can not edit "Lernende" for any courses
        Given I am logged in via browser as "test-student@sandstorm.de"
        # we can't navigate to the page via mouse as it shouldn't even be visible to us
        When I visit url "/exercise-overview/<courseId>/course-members"
        Then the response status code should be 403
        And the page should contain the text "Zugriff verweigert"

        Examples:
            | courseId |
            | course1  |
            | course2  |

    Scenario Outline: As student I can not edit any course
        Given I am logged in via browser as "test-student@sandstorm.de"
        When I visit url "/exercise-overview/course/edit/<courseId>"
        Then the response status code should be 403
        And the page should contain the text "Zugriff verweigert"

        Examples:
            | courseId |
            | course1  |
            | course2  |

    Scenario Outline: As student I can not delete any courses
        Given I am logged in via browser as "test-student@sandstorm.de"
        When I visit url "/exercise-overview/course/delete/<courseId>"
        Then the response status code should be 403
        And the page should contain the text "Zugriff verweigert"

        Examples:
            | courseId |
            | course1  |
            | course2  |

    Scenario Outline: As student I can not download CSV export for any courses
        Given I am logged in via browser as "test-student@sandstorm.de"
        When I visit url "/exercise-overview/<courseId>/export-csv"
        Then the response status code should be 403
        And the page should contain the text "Zugriff verweigert"

        Examples:
            | courseId |
            | course1  |
            | course2  |
