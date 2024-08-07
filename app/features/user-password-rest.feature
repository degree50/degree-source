@fixtures
Feature: Users can reset their password.
    Users that are registered via email can reset their password.
    SAML (SSO) users can not reset their password.
    The password reset option is shown after the user submitted wrong credentials on the login page.

    @playwright @email
    Scenario: Show password reset option after wrong credentials
        Given A User "resetpw@test.de" with the role "ROLE_STUDENT" exists
        And I visit url "/login"
        When I fill the login form with email "resetpw@test.de" and password "wrongpassword"
        And I click on "Direkter Login"
        Then the page should contain the text "Passwort vergessen?"

        When I click on "Passwort vergessen?"
        Then the page should contain the text "Passwort zurücksetzen"

        When I fill the request password reset form with email "resetpw@test.de"
        When I click on "Absenden"
        Then the page should contain the text "E-Mail zum Passwort-Zurücksetzen versendet"
        And a user with the email address "resetpw@test.de" should have received an email with the subject "Passwort zurücksetzen"

        When I visit the link with the link text "Passwort zurücksetzen" from the email with the subject "Passwort zurücksetzen" sent to "resetpw@test.de"
        Then the page should contain the text "Passwort zurücksetzen"

        When I fill the password reset form with the new password "newpassword123"
        And I click on "Neues Password übernehmen"
        Then the page should contain the text "Ihr Passwort wurde erfolgreich zurückgesetzt"

        When I fill the login form with email "resetpw@test.de" and password "newpassword123"
        And I click on "Direkter Login"
        Then the page contains all the following texts:
            | resetpw@test.de |
            | Schreibtisch    |
            | Meine Aufgaben  |

    @playwright @email
    Scenario: SSO users can not reset their password
        Given A User "sso@test.de" with the role "ROLE_SSO_USER" exists
        And I visit route "app_forgot_password_request"
        Then the page should contain the text "Passwort zurücksetzen"

        When I fill the request password reset form with email "sso@test.de"
        And I click on "Absenden"
        Then the page should contain the text "Dein SAML Password kannst du hier nicht zurücksetzen. Nutze dazu die Funktion von deinem SAML Provider."
        And no email should have been sent
