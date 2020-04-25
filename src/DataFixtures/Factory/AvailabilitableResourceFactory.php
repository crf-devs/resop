<?php

declare(strict_types=1);

namespace App\DataFixtures\Factory;

use App\Domain\DatePeriodCalculator;
use App\Entity\AvailabilitableInterface;
use App\Entity\AvailabilityInterface;
use App\Entity\CommissionableAssetAvailability;
use App\Entity\User;
use App\Entity\UserAvailability;

final class AvailabilitableResourceFactory
{
    public static function create(string $slotInterval, AvailabilitableInterface $resource, string $startTime, string $status = AvailabilityInterface::STATUS_AVAILABLE): AvailabilityInterface
    {
        $startDate = DatePeriodCalculator::roundToDailyInterval(new \DateTimeImmutable($startTime), \DateInterval::createFromDateString($slotInterval));

        $availabilityClass = User::class === \get_class($resource) ? UserAvailability::class : CommissionableAssetAvailability::class;

        return new $availabilityClass(
            null,
            $resource,
            $startDate,
            $startDate->add(\DateInterval::createFromDateString($slotInterval)),
            $status
        );
    }
}
