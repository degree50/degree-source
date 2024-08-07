@fixtures @playwright
Feature: Copy Exercises

    # This feature introduces the ability to copy an exercise.
    #
    # Copying an Exercise creates a new Exercise that will be added to a specified course.
    #   * Exercise:
    #       * id -> new
    #       * name -> copied
    #       * description -> copied
    #       * creator -> new (copying user)
    #       * createdAt -> new
    #       * course -> new
    #       * userExerciseInteractions -> new
    #       * phases -> created new out of existing ones (see below)
    #   * Phase
    #       * id -> new
    #       * belongsToExercise -> new
    #       * name -> copied
    #       * task -> copied
    #       * isGroupPhase -> copied
    #       * sorting -> copied
    #       * otherSolutionsAreAccessible -> copied
    #       * teams -> new
    #       * dependsOnPhase -> points to new (get via sorting of previous dependsOnPhase)
    #       * type -> copied
    #       * videos -> copied (and made available for the course)
    #       * videoAnnotationsActive -> copied
    #       * videoCodesActive -> copied
    #       * anhaenge -> copied
    #       * videoCodePrototypes -> new from existing
    #   * VideoCodePrototype
    #       * id -> new
    #       * name -> copy
    #       * description -> copy
    #       * color -> copy

    Scenario: Copy Exercise with ExercisePhases
        Given A User 'test-admin@sandstorm.de' with the role 'ROLE_ADMIN' exists

        And A Course with ID 'course1' exists
        And A Course with ID 'course2' exists

        And I am logged in as 'test-admin@sandstorm.de'
        And A Video with Id "video1" created by User "test-admin@sandstorm.de" exists
        And the Video with Id "video1" is added to Course "course1"

        And An Exercise with the following data exists:
            | id   | name      | description              | creator                 | course  |
            | e1ID | exercise1 | description of exercise1 | test-admin@sandstorm.de | course1 |

        And An ExercisePhase with the following data exists:
            | id | name           | task                          | isGroupPhase | sorting | otherSolutionsAreAccessible | belongsToExercise | dependsOnPhase | type          | videoAnnotationsActive | videoCodesActive |
            | p1 | exercisePhase1 | description of exercisePhase1 | false        | 0       | true                        | e1ID              | null           | videoAnalysis | true                   | true             |
            | p2 | exercisePhase2 | description of exercisePhase2 | true         | 1       | true                        | e1ID              | p1             | videoCutting  |                        |                  |

        And The Video with Id "video1" is added to exercisePhase with Id "p1"
        And An Attachment with Id 'attachment1' created by User 'test-admin@sandstorm.de' exists for ExercisePhase 'p1'

        And I have a predefined videoCodePrototype belonging to exercise phase p1 and with properties
            | id         | name       | color   |
            | videoCode1 | VideoCode1 | #ffffff |
        And I have a predefined videoCodePrototype belonging to exercise phase p1 and with properties
            | id         | name       | color   |
            | videoCode2 | VideoCode2 | #000000 |

        When I am logged in via browser as 'test-admin@sandstorm.de'
        And I click on the button with the aria label 'Aktionen ein/ausblenden'
        And I click on 'Aufgabe kopieren'
        And I click on 'course2'
        And I submit the form
        And I visit url '/exercise-overview/course2'

        Then the page should contain the text 'exercise1'
        And the page should not contain the text 'e1ID'

        When I click on the button with the aria label 'Aktionen ein/ausblenden'
        And I click on 'Aufgabe bearbeiten'

        Then the page should contain the text 'exercisePhase1'
        And the page should contain the text 'exercisePhase2'

        When I click on first element with testId 'exercisePhaseEditButton'

        Then the page should contain the text 'VideoCode1'
        And the page should contain the text 'VideoCode2'
        And the page should contain the text 'TEST_Video_video1'
        And the page should contain the text 'TEST_ATTACHMENT_attachment1'
