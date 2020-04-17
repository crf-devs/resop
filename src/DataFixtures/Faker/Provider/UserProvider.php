<?php

declare(strict_types=1);

namespace App\DataFixtures\Faker\Provider;

use App\Domain\SkillSetDomain;
use Faker\Provider\Base as Faker;

class UserProvider
{
    private SkillSetDomain $skillSetDomain;

    public function __construct(SkillSetDomain $skillSetDomain)
    {
        $this->skillSetDomain = $skillSetDomain;
    }

    public function randomSkillSet(): array
    {
        return Faker::randomElements($this->skillSetDomain->getSkillSetKeys(), random_int(1, 3));
    }
}
