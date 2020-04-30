<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Behat\Mink\Exception\DriverException;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\MinkExtension\Context\RawMinkContext;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use PantherExtension\Driver\PantherDriver;

final class TraversingContext extends RawMinkContext
{
    private string $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

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
     * @When I wait for the element :modal to load
     */
    public function iWaitForElementVisibility(string $cssElementSelector): void
    {
        $driver = $this->getSession()->getDriver();

        if (!$driver instanceof PantherDriver) {
            throw new DriverException('PantherDriver is mandatory for this context. You should use "@javascript" on your scenario.');
        }

        /**
         * related to: https://github.com/Guikingone/panther-extension/issues/7
         * @phpstan-ignore-next-line
         */
        $driver->wait(5, WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector($cssElementSelector)));
    }

    /**
     * @Then I take a screenshot
     * @Then I take a screenshot with name :name
     */
    public function iTakeAScreenshot(string $name = null): void
    {
        $this->saveScreenshot($name ? "$name.png" : null, sprintf('%s/var', $this->projectDir));
    }
}
