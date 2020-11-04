<?php

declare(strict_types=1);

namespace App\DataFixtures\Faker\Provider;

class DateTimeProvider
{
    public function dateTimeImmutable(string $time): \DateTimeImmutable
    {
        return new \DateTimeImmutable($time);
    }
}
