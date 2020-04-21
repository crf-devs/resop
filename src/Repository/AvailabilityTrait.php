<?php

declare(strict_types=1);

namespace App\Repository;

trait AvailabilityTrait
{
    /**
     * Round a \DateTimeImmutable to closest interval.
     * Use $floor=true to subtract diff (`floor`), or $floor=false to add diff (`ceil`).
     */
    private static function round(\DateTimeImmutable $date, string $slotInterval, bool $floor = true): \DateTimeImmutable
    {
        $pattern = '/^\+(\d+) hours?$/';
        if (!preg_match($pattern, $slotInterval)) {
            throw new \InvalidArgumentException(sprintf('Invalid slot interval, expecting format like "+<int> hours", got "%s".', $slotInterval));
        }

        $interval = (int) preg_replace($pattern, '$1', $slotInterval);
        $date = $date->setTime((int) $date->format('H'), 0, 0, 0);
        if (0 !== ($diff = (int) $date->format('H') % $interval)) {
            $date = $date->modify(sprintf('%s%d hours', $floor ? '-' : '+', $diff));
        }

        return $date;
    }
}
