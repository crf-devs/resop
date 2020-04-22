<?php

declare(strict_types=1);

namespace App\DataFixtures\Faker\Provider;

use App\Domain\SkillSetDomain;
use Faker\Provider\Base as Faker;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;

class UserProvider
{
    private SkillSetDomain $skillSetDomain;
    private PhoneNumberUtil $phoneNumberUtil;

    public function __construct(SkillSetDomain $skillSetDomain, PhoneNumberUtil $phoneNumberUtil)
    {
        $this->skillSetDomain = $skillSetDomain;
        $this->phoneNumberUtil = $phoneNumberUtil;
    }

    public function randomSkillSet(): array
    {
        return Faker::randomElements($this->skillSetDomain->getSkillSetKeys(), random_int(1, 3));
    }

    public function phoneNumberObject(string $phoneNumber, ?string $defaultRegion = 'FR'): PhoneNumber
    {
        return $this->phoneNumberUtil->parse($phoneNumber, $defaultRegion);
    }
}
