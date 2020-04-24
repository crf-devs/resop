<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Behat\Mink\Exception\ElementNotFoundException;
use Behat\MinkExtension\Context\RawMinkContext;

final class TraversingContext extends RawMinkContext
{
    /**
     * Click on third link:
     * Example: When I follow "link" at position 2
     *
     * Click on last link:
     * Example: When I follow "link" at position -1
     *
     * @When /^(?:|I )follow "(?P<link>(?:[^"]|\\")*)" at position (-?\d+)$/
     *
     * @throws ElementNotFoundException
     */
    public function clickLastLink(string $link, int $position): void
    {
        $link = str_replace('\\"', '"', $link);
        $links = $this->getSession()->getPage()->findAll('named', ['link', $link]);

        $index = $position < 0 ? \count($links) + $position : $position;

        if (!\array_key_exists($index, $links)) {
            throw new ElementNotFoundException($this->getSession(), 'link', 'id|title|alt|text', $link);
        }

        $links[$index]->click();
    }
}
