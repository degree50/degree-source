@fixtures @playwright
Feature: Roles and constraints regarding viewing, creating, editing and deletion of users

    # role constrains on user entity
    #
    # admin can
    #   * go to admin page and view, edit, create, delete users
    # dozent & student
    #   * can NOT go to admin page and NOT view, edit, create, delete users
    #
    #   ┌──────────┬────────────────────┬────────────────────┬───────────┬───────────┐
    #   │  Action  │        View        │       Create       │  Edit     │  Delete   │
    #   ├──────────┼────────────────────┼────────────────────┼───────────┼───────────┤
    #   │  admin   │  ALL               │  All Courses       │  ALL      │  ALL      │
    #   ├──────────┼────────────────────┼────────────────────┼───────────┼───────────┤
    #   │  dozent  │  NONE              │  NONE              │  NONE     │  NONE     │
    #   ├──────────┼────────────────────┼────────────────────┼───────────┼───────────┤
    #   │  student │  NONE              │  NONE              │  NONE     │  NONE     │
    #   └──────────┴────────────────────┴────────────────────┴───────────┴───────────┘
    #

    Background:
        Given A User "admin@sandstorm.de" with the role "ROLE_ADMIN" exists
        And A User "dozent@sandstorm.de" with the role "ROLE_DOZENT" exists
        And A User "student@sandstorm.de" with the role "ROLE_STUDENT" exists

    Scenario: As admin I can go to admin panel
        Given I am logged in via browser as "admin@sandstorm.de"
        When I visit route "admin-panel"
        Then the response status code should be 200

    Scenario: As dozent I can not go to admin panel
        Given I am logged in via browser as "dozent@sandstorm.de"
        When I visit route "admin-panel"
        Then the response status code should be 403

    Scenario: As student I can not go to admin panel
        Given I am logged in via browser as "student@sandstorm.de"
        When I visit route "admin-panel"
        Then the response status code should be 403
