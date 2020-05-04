<?php

declare(strict_types=1);

namespace App\DataFixtures\Factory;

use App\Domain\DatePeriodCalculator;
use App\Entity\AvailabilitableInterface;
use App\Entity\AvailabilityInterface;
use App\Entity\CommissionableAsset;
use App\Entity\CommissionableAssetAvailability;
use App\Entity\User;
use App\Entity\UserAvailability;

final class AvailabilitableResourceFactory
{
    public static function create(string $slotInterval, AvailabilitableInterface $resource, string $startTime, string $status = AvailabilityInterface::STATUS_AVAILABLE): AvailabilityInterface
    {
        $startDate = DatePeriodCalculator::roundToDailyInterval(new \DateTimeImmutable($startTime), \DateInterval::createFromDateString($slotInterval));

        $endTime = $startDate->add(\DateInterval::createFromDateString($slotInterval));

        if ($resource instanceof User) {
            return new UserAvailability(null, $resource, $startDate, $endTime, $status);
        }

        if ($resource instanceof CommissionableAsset) {
            return new CommissionableAssetAvailability(null, $resource, $startDate, $endTime, $status);
        }

        throw new \LogicException(sprintf('Not handled resource of type "%s"', \get_class($resource)));
    }
}
