@fixtures
Feature: Users can register via a registration form.
    This is used for users that do not have an SAML (SSO) account with the hosting organization (e.g. university).
    Those users have to verify their email address before they can use the platform.
    Newly registered users are automatically assigned the role "ROLE_STUDENT".

    @playwright @email
    Scenario: User can register
        Given I visit route "app_register"
        When I fill the registration form with email "register@test.de" and password "test1234"
        And I click on "Registrieren"
        Then the page should contain the text "Sie haben sich erfolgreich registriert. Bitte bestätigen Sie Ihre E-Mail-Adresse."
        And User with Username "register@test.de" should exist
        And User "register@test.de" should only have the role "ROLE_STUDENT"
        And User "register@test.de" should be marked as not notified
        And a user with the email address "register@test.de" should have received an email with the subject "Bestätigen Sie Ihre E-Mail-Adresse"

    @playwright
    Scenario: User logs in when not verified
        Given A User "notverified@test.de" with the role "ROLE_STUDENT" exists
        And User "notverified@test.de" is marked as not verified
        When I am logged in via browser as "notverified@test.de"
        Then the page should contain the text "Bitte verifizieren Sie Ihre E-Mail-Adresse."

    @playwright @email
    Scenario: User can verify email address
        Given I visit route "app_register"
        When I fill the registration form with email "register@test.de" and password "test1234"
        And I click on "Registrieren"
        When I visit the link with the link text "E-Mail-Adresse bestätigen" from the email with the subject "Bestätigen Sie Ihre E-Mail-Adresse" sent to "register@test.de"
        Then the page should contain the text "Ihre E-Mail-Adresse wurde erfolgreich bestätigt."

    @playwright
    Scenario: User logs in when verified
        Given A User "verified@test.de" with the role "ROLE_STUDENT" exists
        And User "verified@test.de" is marked as verified
        When I am logged in via browser as "verified@test.de"
        Then the page contains all the following texts:
            | verified@test.de |
            | Schreibtisch     |
            | Meine Aufgaben   |
