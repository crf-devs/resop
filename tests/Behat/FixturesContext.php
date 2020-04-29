<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\LoaderInterface as AliceBundleLoaderInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpKernel\KernelInterface;

final class FixturesContext implements Context
{
    private AliceBundleLoaderInterface $aliceFixturesLoader;
    private KernelInterface $kernel;
    private EntityManagerInterface $entityManager;

    public function __construct(AliceBundleLoaderInterface $aliceFixturesLoader, KernelInterface $kernel, EntityManagerInterface $entityManager)
    {
        $this->aliceFixturesLoader = $aliceFixturesLoader;
        $this->kernel = $kernel;
        $this->entityManager = $entityManager;
    }

    /**
     * @AfterScenario @javascript
     */
    public function loadFixtures(): void
    {
        $this->aliceFixturesLoader->load(
            new Application($this->kernel),
            $this->entityManager,
            [],
            'test',
            false,
            true
        );
    }
}
