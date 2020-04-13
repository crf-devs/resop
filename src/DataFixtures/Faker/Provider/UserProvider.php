<?php

declare(strict_types=1);

namespace App\DataFixtures\Faker\Provider;

use App\Domain\SkillSetDomain;

class UserProvider
{
    private SkillSetDomain $skillSetDomain;

    public function __construct(SkillSetDomain $skillSetDomain)
    {
        $this->skillSetDomain = $skillSetDomain;
    }

    public function randomSkillSet(): array
    {
        return (array) array_rand($this->skillSetDomain->getSkillSetKeys(), random_int(1, 3));
    }
}
