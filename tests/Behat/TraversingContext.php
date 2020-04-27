<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Behat\Mink\Exception\ElementNotFoundException;
use Behat\MinkExtension\Context\RawMinkContext;

final class TraversingContext extends RawMinkContext
{
    /**
     * Click on last link:
     * Example: When I follow the last "link"
     *
     * @When /^(?:|I )follow the last "(?P<link>(?:[^"]|\\")*)"$/
     *
     * @throws ElementNotFoundException
     */
    public function clickLastLink(string $link): void
    {
        $link = str_replace('\\"', '"', $link);
        $links = $this->getSession()->getPage()->findAll('named', ['link', $link]);

        if (0 === \count($links)) {
            throw new ElementNotFoundException($this->getSession(), 'link', 'id|title|alt|text', $link);
        }

        $links[\count($links) - 1]->click();
    }
}
