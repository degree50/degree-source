@fixtures
Feature: Users expire and are removed automatically. They are notified before expiration and can extend their account.

    @integration
    Scenario: Remove Student after expiration
        Given A User "student@test.de" with the role "ROLE_STUDENT" exists
        And the expiration date of user "student@test.de" is set to "-1 day" from now
        When expired users are removed
        Then No User with Username "student@test.de" does exist

    @integration
    Scenario: Do not remove Admin after expiration
        Given A User "admin@test.de" with the role "ROLE_ADMIN" exists
        And the expiration date of user "admin@test.de" is set to "-1 day" from now
        When expired users are removed
        Then User with Username "admin@test.de" should exist

    @integration
    Scenario: Do not remove Dozent after expiration
        And A User "dozent@test.de" with the role "ROLE_DOZENT" exists
        And the expiration date of user "dozent@test.de" is set to "-1 day" from now
        When expired users are removed
        Then User with Username "dozent@test.de" should exist

    @integration @email
    Scenario: User is notified before expiration
        Given A User "student@test.de" with the role "ROLE_STUDENT" exists
        And the expiration date of user "student@test.de" is set to "+8 month -1 day" from now
        When expiring users are notified
        Then User "student@test.de" should be marked as notified
        And a user with the email address "student@test.de" should have received an email with the subject "Account-Verlängerung"

    @playwright
    Scenario: User can extend account
        Given A User "student@test.de" with the role "ROLE_STUDENT" exists
        And the expiration date of user "student@test.de" is set to "+8 month -1 day" from now
        And I am logged in via browser as "student@test.de"
        When I visit route "app_increase_user_expiration_date"
        And I click on "Account um 1 Jahr verlängern"
        Then the page should contain the text "Dein Account wurde erfolgreich verlängert."
        And the expiration date of user "student@test.de" should be set to "+1 yeah +8 month -1 day" from now
        And User "student@test.de" should be marked as not notified

    @playwright
    Scenario: User can not extend account before before expiration notice time window to prevent users from extending their account for more than 1 year at a time.
        Given A User "student@test.de" with the role "ROLE_STUDENT" exists
        And the expiration date of user "student@test.de" is set to "+8 month +1 day" from now
        And I am logged in via browser as "student@test.de"
        When I visit route "app_increase_user_expiration_date"
        Then the page should contain the text "Dein Account kann vorerst nicht weiter verlängert werden."
        And the expiration date of user "student@test.de" should be set to "+8 month +1 day" from now
