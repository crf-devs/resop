<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Alex\MailCatcher\Behat\MailCatcherContext;
use Alex\MailCatcher\Message;
use Symfony\Component\DomCrawler\Crawler;

class MailsContext extends MailCatcherContext
{
    use MinkContextTrait;

    /**
     * @Then /^I click on the "([^"]+)" link in mail$/
     */
    public function clickOnLinkInMail(string $selector): void
    {
        $crawler = $this->getCrawler($this->getCurrentMessage());
        $link = $crawler->filter($selector);

        if (!$link->count()) {
            throw new \RuntimeException("Unable to find the $selector link in mail");
        }

        if (empty($link->attr('href'))) {
            throw new \RuntimeException("The $selector link does not have any href");
        }

        $this->getMinkContext()->visitPath((string) $link->attr('href'));
    }

    private function getCrawler(Message $message): Crawler
    {
        if (!$message->isMultipart() || !$message->hasPart('text/html')) {
            throw new \RuntimeException(sprintf('The current message has no html part.'));
        }

        return new Crawler($message->getPart('text/html')->getContent());
    }

    private function getCurrentMessage(): Message
    {
        if (null === $this->currentMessage) {
            throw new \RuntimeException('No message selected');
        }

        return $this->currentMessage;
    }
}
