@fixtures @playwright
Feature: Roles and constraints regarding viewing, creating, editing and deletion of videos

    # role constrains on video entity
    #
    # admin can
    #   * view, edit, delete videos of all Users
    #   * create videos for all courses
    # dozent & student can
    #   * view, edit, delete videos of himself
    #   * create videos for all assigned courses
    # dozent
    #   * can view, edit, delete videos of his courses
    # student without course can
    #   * view, edit, delete videos of himself
    #
    #   ┌──────────┬────────────────────┬────────────────────┬───────────┬────────────────────┐
    #   │  Action  │        View        │       Create       │  Edit     │  Delete            │
    #   ├──────────┼────────────────────┼────────────────────┼───────────┼────────────────────┤
    #   │  admin   │  ALL               │  All courses       │  ALL      │  ALL               │
    #   ├──────────┼────────────────────┼────────────────────┼───────────┼────────────────────┤
    #   │  dozent  │  Created | course  │  Assigned courses  │  Created  │  Created | course  │
    #   ├──────────┼────────────────────┼────────────────────┼───────────┼────────────────────┤
    #   │  student │  Created | course  │  Assigned courses  │  Created  │  Created           │
    #   └──────────┴────────────────────┴────────────────────┴───────────┴────────────────────┘
    #

    Background:
        Given A User "test-admin@sandstorm.de" with the role "ROLE_ADMIN" exists
        And A User "test-dozent@sandstorm.de" with the role "ROLE_DOZENT" exists
        And A User "test-student@sandstorm.de" with the role "ROLE_STUDENT" exists
        And A User "student-without-course@sandstorm.de" with the role "ROLE_STUDENT" exists

        And A Course with ID "course1" exists
        And A Course with ID "course2" exists

        And The User "test-dozent@sandstorm.de" has CourseRole "DOZENT" in Course course1
        And The User "test-student@sandstorm.de" has CourseRole "STUDENT" in Course course1

        And A Video with Id "admin_video_no_course" created by User "test-admin@sandstorm.de" exists
        And A Video with Id "admin_video_course1" created by User "test-admin@sandstorm.de" exists
        And A Video with Id "admin_video_course2" created by User "test-admin@sandstorm.de" exists
        And the Video with Id "admin_video_course1" is added to Course "course1"
        And the Video with Id "admin_video_course2" is added to Course "course2"

        And A Video with Id "dozent_video_no_course" created by User "test-dozent@sandstorm.de" exists
        And A Video with Id "dozent_video_course1" created by User "test-dozent@sandstorm.de" exists
        And the Video with Id "dozent_video_course1" is added to Course "course1"

        And A Video with Id "student_video_no_course" created by User "test-student@sandstorm.de" exists
        And A Video with Id "student_video_course1" created by User "test-student@sandstorm.de" exists
        And the Video with Id "student_video_course1" is added to Course "course1"

    #########################
    ### View
    #########################
    Scenario: As admin I can see the view option for all videos in Mediathek
        Given I am logged in via browser as "test-admin@sandstorm.de"
        When I visit route "mediathek--index"
        Then the page contains all the following texts:
            | /video/play/admin_video_no_course   |
            | /video/play/admin_video_course1     |
            | /video/play/admin_video_course2     |
            | /video/play/dozent_video_no_course  |
            | /video/play/dozent_video_course1    |
            | /video/play/student_video_no_course |
            | /video/play/student_video_course1   |

    Scenario Outline: As admin I can view all videos
        Given I am logged in via browser as "test-admin@sandstorm.de"
        When I visit url "<url>"
        Then the response status code should be "<statusCode>"
        And the page should contain the text "<text>"

        Examples:
            | url                                 | statusCode | text                    |
            | /video/play/admin_video_no_course   | 200        | admin_video_no_course   |
            | /video/play/admin_video_course1     | 200        | admin_video_course1     |
            | /video/play/admin_video_course2     | 200        | admin_video_course2     |
            | /video/play/dozent_video_no_course  | 200        | dozent_video_no_course  |
            | /video/play/dozent_video_course1    | 200        | dozent_video_course1    |
            | /video/play/student_video_no_course | 200        | student_video_no_course |
            | /video/play/student_video_course1   | 200        | student_video_course1   |

    Scenario: As dozent I can see the view option only for my videos and videos of assigned courses
        Given I am logged in via browser as "test-dozent@sandstorm.de"
        When I visit route "mediathek--index"
        Then the page contains all the following texts:
            | /video/play/admin_video_course1    |
            | /video/play/dozent_video_no_course |
            | /video/play/dozent_video_course1   |
            | /video/play/student_video_course1  |
        And the page contains none of the following texts:
            | /video/play/admin_video_no_course   |
            | /video/play/admin_video_course2     |
            | /video/play/student_video_no_course |

    Scenario Outline: As dozent I can view only my videos and videos of assigned courses
        Given I am logged in via browser as "test-dozent@sandstorm.de"
        When I visit url "<url>"
        Then the response status code should be "<statusCode>"
        And the page should contain the text "<text>"

        Examples:
            | url                                 | statusCode | text                   |
            | /video/play/admin_video_course1     | 200        | admin_video_course1    |
            | /video/play/dozent_video_no_course  | 200        | dozent_video_no_course |
            | /video/play/dozent_video_course1    | 200        | dozent_video_course1   |
            | /video/play/student_video_course1   | 200        | student_video_course1  |
            | /video/play/admin_video_no_course   | 403        | Zugriff verweigert     |
            | /video/play/admin_video_course2     | 403        | Zugriff verweigert     |
            | /video/play/student_video_no_course | 403        | Zugriff verweigert     |

    Scenario: As student I can see the view option only for my videos and videos of assigned courses
        Given I am logged in via browser as "test-student@sandstorm.de"
        When I visit route "mediathek--index"
        Then the page contains all the following texts:
            | /video/play/admin_video_course1     |
            | /video/play/dozent_video_course1    |
            | /video/play/student_video_no_course |
            | /video/play/student_video_course1   |
        And the page contains none of the following texts:
            | /video/play/admin_video_no_course  |
            | /video/play/admin_video_course2    |
            | /video/play/dozent_video_no_course |

    Scenario Outline: As student I can view only my videos and videos of assigned courses
        Given I am logged in via browser as "test-student@sandstorm.de"
        When I visit url "<url>"
        Then the response status code should be "<statusCode>"
        And the page should contain the text "<text>"

        Examples:
            | url                                 | statusCode | text                    |
            | /video/play/admin_video_course1     | 200        | admin_video_course1     |
            | /video/play/student_video_course1   | 200        | student_video_course1   |
            | /video/play/student_video_no_course | 200        | student_video_no_course |
            | /video/play/dozent_video_course1    | 200        | dozent_video_course1    |
            | /video/play/admin_video_no_course   | 403        | Zugriff verweigert      |
            | /video/play/admin_video_course2     | 403        | Zugriff verweigert      |
            | /video/play/dozent_video_no_course  | 403        | Zugriff verweigert      |

    #########################
    ### Create
    #########################
    Scenario: As admin I can create videos for every course
        Given I am logged in via browser as "test-admin@sandstorm.de"
        When I visit route "mediathek__video--upload"
        Then the response status code should be 200
        And the page contains all the following texts:
            | course1 |
            | course2 |

    Scenario: As dozent I can create videos for myself and assigned courses
        Given I am logged in via browser as "test-dozent@sandstorm.de"
        When I visit route "mediathek__video--upload"
        Then the response status code should be 200
        Then the page contains all the following texts:
            | course1 |

    Scenario: As student I can create videos for myself and assigned courses
        Given I am logged in via browser as "test-student@sandstorm.de"
        When I visit route "mediathek__video--upload"
        Then the response status code should be 200
        Then the page contains all the following texts:
            | course1 |

    Scenario: As student without any assigned courses I can not create videos
        Given I am logged in via browser as "student-without-course@sandstorm.de"
        When I visit route "mediathek__video--upload"
        Then the response status code should be 403

    #########################
    ### Edit
    #########################
    Scenario: As admin I can see the edit option for all videos
        Given I am logged in via browser as "test-admin@sandstorm.de"
        When I visit route "mediathek--index"
        Then the page contains all the following texts:
            | /video/edit/admin_video_no_course   |
            | /video/edit/admin_video_course1     |
            | /video/edit/admin_video_course2     |
            | /video/edit/dozent_video_no_course  |
            | /video/edit/dozent_video_course1    |
            | /video/edit/student_video_no_course |
            | /video/edit/student_video_course1   |

    Scenario Outline: As admin I can access the edit page of all videos
        Given I am logged in via browser as "test-admin@sandstorm.de"
        When I visit url "<url>"
        Then the response status code should be "<statusCode>"
        And the page should contain the text "<text>"

        Examples:
            | url                                 | statusCode | text             |
            | /video/edit/admin_video_no_course   | 200        | Video bearbeiten |
            | /video/edit/admin_video_course1     | 200        | Video bearbeiten |
            | /video/edit/admin_video_course2     | 200        | Video bearbeiten |
            | /video/edit/dozent_video_no_course  | 200        | Video bearbeiten |
            | /video/edit/dozent_video_course1    | 200        | Video bearbeiten |
            | /video/edit/student_video_no_course | 200        | Video bearbeiten |
            | /video/edit/student_video_course1   | 200        | Video bearbeiten |

    Scenario: As dozent I can see the edit option for all my videos and videos in my courses
        Given I am logged in via browser as "test-dozent@sandstorm.de"
        When I visit route "mediathek--index"
        Then the page contains all the following texts:
            | /video/edit/dozent_video_no_course |
            | /video/edit/dozent_video_course1   |
            | /video/edit/student_video_course1  |
            | /video/edit/admin_video_course1    |
        And the page contains none of the following texts:
            | /video/edit/admin_video_no_course   |
            | /video/edit/admin_video_course2     |
            | /video/edit/student_video_no_course |

    Scenario Outline: As dozent I can access the edit page for all my videos and videos in my courses
        Given I am logged in via browser as "test-dozent@sandstorm.de"
        When I visit url "<url>"
        Then the response status code should be "<statusCode>"
        And the page should contain the text "<text>"

        Examples:
            | url                                 | statusCode | text               |
            | /video/edit/dozent_video_no_course  | 200        | Video bearbeiten   |
            | /video/edit/dozent_video_course1    | 200        | Video bearbeiten   |
            | /video/edit/admin_video_course1     | 200        | Video bearbeiten   |
            | /video/edit/student_video_course1   | 200        | Video bearbeiten   |
            | /video/edit/admin_video_no_course   | 403        | Zugriff verweigert |
            | /video/edit/admin_video_course2     | 403        | Zugriff verweigert |
            | /video/edit/student_video_no_course | 403        | Zugriff verweigert |

    Scenario: As student I can see the edit option only for my created videos
        Given I am logged in via browser as "test-student@sandstorm.de"
        When I visit route "mediathek--index"
        Then the page contains all the following texts:
            | /video/edit/student_video_no_course |
            | /video/edit/student_video_course1   |
        And the page contains none of the following texts:
            | /video/edit/admin_video_no_course  |
            | /video/edit/admin_video_course1    |
            | /video/edit/admin_video_course2    |
            | /video/edit/dozent_video_no_course |
            | /video/edit/dozent_video_course1   |

    Scenario Outline: As student I can access the edit page of my videos only
        Given I am logged in via browser as "test-student@sandstorm.de"
        When I visit url "<url>"
        Then the response status code should be "<statusCode>"
        And the page should contain the text "<text>"

        Examples:
            | url                                 | statusCode | text               |
            | /video/edit/student_video_no_course | 200        | Video bearbeiten   |
            | /video/edit/student_video_course1   | 200        | Video bearbeiten   |
            | /video/edit/admin_video_no_course   | 403        | Zugriff verweigert |
            | /video/edit/admin_video_course1     | 403        | Zugriff verweigert |
            | /video/edit/admin_video_course2     | 403        | Zugriff verweigert |
            | /video/edit/dozent_video_no_course  | 403        | Zugriff verweigert |
            | /video/edit/dozent_video_course1    | 403        | Zugriff verweigert |

    #########################
    ### Delete
    #########################
    Scenario: As admin I can see the delete option for all videos
        Given I am logged in via browser as "test-admin@sandstorm.de"
        When I visit route "mediathek--index"
        Then the page contains all the following texts:
            | /video/delete/admin_video_no_course   |
            | /video/delete/admin_video_course1     |
            | /video/delete/admin_video_course2     |
            | /video/delete/dozent_video_no_course  |
            | /video/delete/dozent_video_course1    |
            | /video/delete/student_video_no_course |
            | /video/delete/student_video_course1   |

    Scenario Outline: As admin I can delete all videos
        Given I am logged in via browser as "test-admin@sandstorm.de"
        When I visit url "<url>"
        Then the response status code should be "<statusCode>"
        And the page should contain the text "<text>"

        Examples:
            | url                                     | statusCode | text                        |
            | /video/delete/admin_video_no_course/1   | 200        | Video erfolgreich gelöscht! |
            | /video/delete/admin_video_course1/1     | 200        | Video erfolgreich gelöscht! |
            | /video/delete/admin_video_course2/1     | 200        | Video erfolgreich gelöscht! |
            | /video/delete/dozent_video_no_course/1  | 200        | Video erfolgreich gelöscht! |
            | /video/delete/dozent_video_course1/1    | 200        | Video erfolgreich gelöscht! |
            | /video/delete/student_video_no_course/1 | 200        | Video erfolgreich gelöscht! |
            | /video/delete/student_video_course1/1   | 200        | Video erfolgreich gelöscht! |

    Scenario: As dozent I can see the delete option for all my videos and videos in my courses
        Given I am logged in via browser as "test-dozent@sandstorm.de"
        When I visit route "mediathek--index"
        Then the page contains all the following texts:
            | /video/delete/dozent_video_no_course |
            | /video/delete/dozent_video_course1   |
            | /video/delete/admin_video_course1    |
            | /video/delete/student_video_course1  |
        And the page contains none of the following texts:
            | /video/delete/admin_video_no_course   |
            | /video/delete/admin_video_course2     |
            | /video/delete/student_video_no_course |

    Scenario Outline: As dozent I delete my created videos and videos in my courses
        Given I am logged in via browser as "test-dozent@sandstorm.de"
        When I visit url "<url>"
        Then the response status code should be "<statusCode>"
        And the page should contain the text "<text>"

        Examples:
            | url                                     | statusCode | text                        |
            | /video/delete/dozent_video_no_course/1  | 200        | Video erfolgreich gelöscht! |
            | /video/delete/dozent_video_course1/1    | 200        | Video erfolgreich gelöscht! |
            | /video/delete/admin_video_course1/1     | 200        | Video erfolgreich gelöscht! |
            | /video/delete/student_video_course1/1   | 200        | Video erfolgreich gelöscht! |
            | /video/delete/admin_video_no_course/1   | 403        | Zugriff verweigert          |
            | /video/delete/admin_video_course2/1     | 403        | Zugriff verweigert          |
            | /video/delete/student_video_no_course/1 | 403        | Zugriff verweigert          |

    Scenario: As student I can see the delete option only for my created videos
        Given I am logged in via browser as "test-student@sandstorm.de"
        When I visit route "mediathek--index"
        Then the page contains all the following texts:
            | /video/delete/student_video_no_course |
            | /video/delete/student_video_course1   |
        And the page contains none of the following texts:
            | /video/delete/admin_video_no_course  |
            | /video/delete/admin_video_course1    |
            | /video/delete/admin_video_course2    |
            | /video/delete/dozent_video_no_course |
            | /video/delete/dozent_video_course1   |

    Scenario Outline: As student I can delete only my created videos
        Given I am logged in via browser as "test-student@sandstorm.de"
        When I visit url "<url>"
        Then the response status code should be "<statusCode>"
        And the page should contain the text "<text>"

        Examples:
            | url                                     | statusCode | text                        |
            | /video/delete/student_video_no_course/1 | 200        | Video erfolgreich gelöscht! |
            | /video/delete/student_video_course1/1   | 200        | Video erfolgreich gelöscht! |
            | /video/delete/admin_video_no_course/1   | 403        | Zugriff verweigert          |
            | /video/delete/admin_video_course1/1     | 403        | Zugriff verweigert          |
            | /video/delete/admin_video_course2/1     | 403        | Zugriff verweigert          |
            | /video/delete/dozent_video_no_course/1  | 403        | Zugriff verweigert          |
            | /video/delete/dozent_video_course1/1    | 403        | Zugriff verweigert          |
