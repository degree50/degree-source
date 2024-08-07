<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotEmpty;
use function PHPUnit\Framework\assertNotNull;

trait EmailTrait
{
    /**
     * @BeforeScenario @email
     * @throws GuzzleException
     */
    public function resetEmails(): void
    {
        $client = new Client();
        $client->delete('http://mailpit:8025/api/v1/messages');
    }

    /**
     * @Then a user with the email address :emailAddress should have received an email with the subject :subject
     */
    public function userShouldReceiveAnEmailWithTheSubject(string $emailAddress, string $subject): void
    {
        $responseBody = $this->findEmailBySubjectAndReceiver($subject, $emailAddress);

        assertNotNull($responseBody, "No email found with the subject '$subject' and receiver '$emailAddress'");
    }

    /**
     * @When I visit the link with the link text :linkText from the email with the subject :subject sent to :emailAddress
     */
    public function iVisitTheLinkWithTheLinkTextFromTheEmailWithTheSubjectSentTo(string $linkText, string $subject, string $emailAddress): void
    {
        $responseBody = $this->findEmailBySubjectAndReceiver($subject, $emailAddress);

        assertNotNull($responseBody, "No email found with the subject '$subject' and receiver '$emailAddress'");

        $emailHtml = $responseBody['HTML'];
        $linkRegexExp = '/<a.*href="(?<link>.*)".*>.*' . $linkText . '.*<\/a>/';
        preg_match($linkRegexExp, $emailHtml, $matches);

        assertNotEmpty($matches, "Link with link text '$linkText' not found in email");

        assertNotEmpty($matches['link'], "Link with link text '$linkText' is empty");

        $unescapedLink = html_entity_decode($matches['link']);

        $this->visitUrl($unescapedLink);
    }

    /**
     * @Given no email should have been sent
     */
    public function noEmailShouldHaveBeenSent(): void
    {
        $responseBody = $this->emailApiGet('/messages');

        assertEquals(0, count($responseBody['messages']));
    }

    /**
     * Get the JSON decoded response body of an email with given subject and receiver.
     *
     * @param string $subject
     * @param string $receiver
     * @return array|null
     */
    private function findEmailBySubjectAndReceiver(string $subject, string $receiver): mixed
    {
        $messagesResponseBody = $this->emailApiGet('/messages');

        $messageId = "";

        foreach($messagesResponseBody['messages'] as $message) {
            foreach($message['To'] as $to) {
                if ($to['Address'] === $receiver && $message['Subject'] === $subject) {
                    $messageId = $message['ID'];
                    break;
                }
            }
        }

        if ($messageId === "") {
            return null;
        }

        return $this->emailApiGet("/message/$messageId");
    }

    /**
     * @param string $apiPath The path to the email api endpoint starting with leading '/'.
     * @return mixed The json decoded response body.
     */
    private function emailApiGet(string $apiPath): mixed
    {
        if (str_starts_with($apiPath, '/') === false) {
            throw new InvalidArgumentException("The api path must start with a leading '/'");
        }

        $client = new Client();
        $messagesResponse = $client->get('http://mailpit:8025/api/v1' . $apiPath);

        assertEquals(200, $messagesResponse->getStatusCode());
        return json_decode($messagesResponse->getBody()->getContents(), true);
    }
}
