<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Behat\Behat\Context\Context;
use Fidry\AliceDataFixtures\LoaderInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class FixturesContext implements Context
{
    private LoaderInterface $loader;

    public function __construct(LoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    /**
     * @BeforeScenario @LoadFixtures
     */
    public function loadFixtures(): void
    {
        $fixtures = Finder::create()
            ->in(__DIR__.'/../../fixtures/')
            ->name(['*.yaml', '*.yml'])
            ->files()
            ->getIterator();

        $this->loader->load(array_map(function (SplFileInfo $file) {
            return $file->getPathname();
        }, iterator_to_array($fixtures)));
    }
}
