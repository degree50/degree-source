<?php

namespace App\Tests\Behat;

use Behat\Gherkin\Node\TableNode;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertStringContainsString;
use function PHPUnit\Framework\assertStringNotContainsString;
use function PHPUnit\Framework\assertTrue;

trait PlaywrightContextTrait
{
    /**
     * @When I visit url :routeName
     */
    public function visitUrl(string $url): void
    {
        // BASEURL is a magic string that will be replaced by the actual base URL
        $urlWithBaseUrl = str_starts_with($url, '/') ? 'BASEURL' . $url : $url;

        $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS
                if (!vars.page) {
                    vars.page = await context.newPage()
                }
                // TODO write first goto and then waitforNavigation
                const response = await vars.page.goto('$urlWithBaseUrl')
                // save response in context
                vars.response = response
            JS
        );
    }

    /**
     * @Then the response status code should be :code
     */
    public function assertResponseStatus(int $code): void
    {
        $actual = $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS
                return vars.response.status();
            JS
        );

        assertEquals($code, $actual);
    }

    /**
     * @Then I am redirected to the login page
     */
    public function iAmRedirectedToTheLoginPage(): void
    {
        $this->thePageShouldContainTheText('Login mit Uni-Account (SSO)');
    }

    /**
     * @Given I am logged in via browser as :username
     */
    public function iAmLoggedInViaBrowserAs($username): void
    {
        $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS
                vars.page = await context.newPage();
                await vars.page.goto("BASEURL/login");
                await vars.page.fill('[name="email"]', '$username');
                await vars.page.fill('[name="password"]', 'password');
                await Promise.all([
                    vars.page.waitForNavigation(),
                    vars.page.click('button[type="submit"]')
                ])
            JS
        );
    }

    private function getPageContent(): string
    {
        return $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS
                return await vars.page.content();
            JS
        );
    }

    /**
     * @Then the page should contain the text :text
     */
    public function thePageShouldContainTheText($text): void
    {
        $content = $this->getPageContent();

        assertStringContainsString($text, $content);
    }

    /**
     * @Then the page should not contain the text :text
     */
    public function thePageShouldNotContainTheText($text): void
    {
        $content = $this->getPageContent();

        assertStringNotContainsString($text, $content);
    }

    /**
     * @Then the page contains all the following texts:
     */
    public function thePageContainsAllTheFollowingTexts(TableNode $tableNode): void
    {
        $this->pageContainsTexts($tableNode->getColumn(0));
    }

    private function pageContainsTexts(array $texts): void
    {
        $content = $this->getPageContent();

        foreach ($texts as $text) {
            assertStringContainsString($text, $content);
        }
    }

    private function pageNotContainTexts(array $texts): void
    {
        $content = $this->getPageContent();

        foreach ($texts as $text) {
            assertStringNotContainsString($text, $content);
        }
    }

    /**
     * @Then the page contains none of the following texts:
     */
    public function thePageContainsNoneTheFollowingTexts(TableNode $tableNode): void
    {
        $this->pageNotContainTexts($tableNode->getColumn(0));
    }

    /**
     * @Given I fill out the course form and submit
     */
    public function iFillOutTheCourseFormAndSubmit(): void
    {
        $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS
                await vars.page.fill('input#course_form_name', 'Test-Kurs')
                await vars.page.click('#course_form_users input[type="checkbox"]')
                await vars.page.click('button#course_form_save')
            JS
        );
    }

    public function waitForSelector($selector): void
    {
        $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS
                await vars.page.waitForSelector('data-test-id=$selector')
            JS
        );
    }

    /**
     * @When I click on :innerText
     */
    public function iClickOn($innerText): void
    {
        $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS

                await vars.page.click('text=$innerText')
            JS
        );
    }

    /**
     * @When I click on the button with the aria label :ariaLabel
     */
    public function iClickOnTheButtonWithTheAriaLabel($ariaLabel): void
    {
        $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS
                await vars.page.click(`[aria-label="$ariaLabel"]`)
            JS
        );
    }

    /**
     * @When I click on first element with testId :testId
     */
    public function iClickOnFirstElementWithTestId($testId): void
    {
        $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS
                await vars.page.click('data-test-id=$testId')
            JS
        );
    }

    /**
     * @Given I submit the form
     */
    public function iSubmitTheForm(): void
    {
        $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS
                await vars.page.click('[type="submit"]')
            JS
        );
    }

    /**
     * @Then I should be able to download the CSV export for :entityId when clicking on :label
     */
    public function iShouldBeAbleToDownloadCsvExport($entityId, $label): void
    {
        $url = $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS
                const [ download ] = await Promise.all(
                    [
                        vars.page.waitForEvent('download'),
                        vars.page.click('[role="button"]:has-text("$label"), button:has-text("$label"), .btn:has-text("$label"), a:has-text("$label")')
                    ]
                )
                const path = await download.url()

                return path
            JS
        );

        assertStringContainsString($entityId, $url);
    }

    /**
     * @Given I fill out the exercise form and submit
     */
    public function iFillOutTheExerciseFormAndSubmit(): void
    {
        $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS
                await vars.page.fill('input#exercise_form_name', 'Test-Aufgabe')
                // here we have to use this way to navigate to the description field because we use ckeditor
                await vars.page.keyboard.press('Tab')
                await vars.page.keyboard.type('Test-Aufgaben-Beschreibung')

                await vars.page.click('button#exercise_form_save')
            JS
        );
    }

    /**
     * @When I select the nth :index element with testId :testId
     */
    public function iSelectTheNthElementWithTestId(int $index, string $testId): void
    {
        $hasElement = $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS
                const element = await vars.page.locator('data-test-id=$testId >> nth=$index')
                vars.selectedElement = element

                return !!element
            JS
        );

        assertTrue($hasElement);
    }

    /**
     * @Then the selected element should have its attribute :attribute set to value :expectedValue
     */
    public function theSelectedElementShouldHaveAttributeSetToValue(string $attribute, string $expectedValue): void
    {
        $actual = $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS
                return vars.selectedElement.getAttribute('$attribute')
            JS
        );

        assertEquals($actual, $expectedValue);
    }

    /**
     * @Then I click on the selected element
     */
    public function iClickOnTheSelectedElement(): void
    {
        $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS
                vars.selectedElement.click()
            JS
        );
    }

    /**
     * @Then :count elements of selectedElement type should exist
     */
    public function numberOfElementsShouldExist($count): void
    {
        $actual = $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS
                return vars.selectedElement.count()
            JS
        );

        assertEquals($count, $actual);
    }

    /**
     * @Then The selected element should have the CSS-class :cssClass
     */
    public function selectedElementShouldHaveCssClass($cssClass): void
    {
        $actualClasses = $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS
                return await vars.selectedElement.getAttribute('class')
            JS
        );

        assertTrue(str_contains($actualClasses, $cssClass));
    }

    /**
     * @Then The selected element should not have the CSS-class :cssClass
     */
    public function selectedElementShouldNotHaveCssClass($cssClass): void
    {
        $actualClasses = $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS
                return await vars.selectedElement.getAttribute('class')
            JS
        );

        assertTrue(!str_contains($actualClasses, $cssClass));
    }

    /**
     * @When I visit route :routeName
     */
    public function visitRoute(string $routeName): void
    {
        $url = $this->router->generate($routeName);

        $this->visitUrl($url);
    }

    /**
     * @When I visit route :routeName with parameters as JSON :jsonParameters
     */
    public function visitRouteWithJsonParameters(string $routeName, string $jsonParameters): void
    {
        $parameters = [];
        if (!empty($jsonParameters)) {
            $parameters = json_decode($jsonParameters, true);
        }

        $url = $this->router->generate($routeName, $parameters);

        $this->visitUrl($url);
    }

    /**
     * @When /^I pause for debugging$/
     */
    public function iPauseForDebugging()
    {
        return $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS
                await vars.page.pause();
            JS
        );
    }

    /**
     * @Then I wait for the event :eventName to be dispatched
     */
    public function iWaitForTheEventToBeDispatched(string $eventName): void
    {
        $this->playwrightConnector->execute($this->playwrightContext, sprintf(
            <<<JS
                // evaluate the event listener
                await vars.page.evaluate(() => {
                    return new Promise((resolve) => {
                        window.addEventListener('%s', () => {
                            resolve();
                        });
                    });
                });
            JS,
            $eventName
        ));
    }

    /**
     * @When I fill the registration form with email :email and password :password
     */
    public function iFillTheRegistrationFormWithEmailAndPassword(string $email, string $password): void
    {
        $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS
                await vars.page.fill('input#registration_form_email', '$email')
                await vars.page.fill('input#registration_form_plainPassword_first', '$password')
                await vars.page.fill('input#registration_form_plainPassword_second', '$password')
            JS
        );
    }

    /**
     * @When I fill the login form with email :email and password :password
     */
    public function iFillTheLoginFormWithEmailAndPassword(string $email, string $password): void
    {
        $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS
                await vars.page.fill('input[name="email"]', '$email')
                await vars.page.fill('input[name="password"]', '$password')
            JS
        );
    }

    /**
     * @When I fill the request password reset form with email :email
     */
    public function iFillTheRequestPasswordResetFormWithEmail(string $email): void
    {
        $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS
                await vars.page.fill('input#reset_password_request_form_email', '$email')
            JS
        );
    }

    /**
     * @When I fill the password reset form with the new password :password
     */
    public function iFillThePasswordResetFormWithTheNewPassword(string $password): void
    {
        $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS
                await vars.page.fill('input#change_password_form_plainPassword_first', '$password')
                await vars.page.fill('input#change_password_form_plainPassword_second', '$password')
            JS
        );
    }

    /**
     * @Then The element with an aria-label starting with text :text should be disabled
     */
    public function theElementWithAnAriaLabelStartingWithTextShouldBeDisabled(string $text): void
    {
        $isDisabled = $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS
                const element = await vars.page.locator('[aria-label^="$text"]')

                return await element.isDisabled()
            JS
        );

        assertTrue($isDisabled);
    }

    /**
     * @Then The element with an aria-label starting with text :text should be enabled
     */
    public function theElementWithAnAriaLabelStartingWithTextShouldBeEnabled(string $text): void
    {
        $isDisabled = $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS
                const element = await vars.page.locator('[aria-label^="$text"]')

                return await element.isDisabled()
            JS
        );

        assertFalse($isDisabled);
    }

    /**
     * @When I click on the element with an aria-label starting with text :text
     */
    public function iClickOnTheElementWithAnAriaLabelStartingWithText(string $text): void
    {
        $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS
                await vars.page.click('[aria-label^="$text"]')
            JS
        );
    }

    /**
     * @When I press the key :key
     *
     * @param string $key see https://developer.mozilla.org/en-US/docs/Web/API/UI_Events/Keyboard_event_key_values
     */
    public function iPressTheKey(string $key): void
    {
        $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS
                await vars.page.keyboard.press('$key')
            JS
        );
    }
}
