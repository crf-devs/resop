<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Behat\Mink\Exception\DriverException;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\MinkExtension\Context\RawMinkContext;
use PantherExtension\Driver\PantherDriver;

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

    /**
     * @When I wait for the modal :modal to load
     */
    public function iWaitForTheModalToLoad(string $modal): void
    {
        $driver = $this->getSession()->getDriver();

        if (!$driver instanceof PantherDriver) {
            throw new DriverException('PantherDriver is mandatory for this context. You should use "@javascript" on your scenario.');
        }

        $driver->waitFor($modal, 2);
    }
}
