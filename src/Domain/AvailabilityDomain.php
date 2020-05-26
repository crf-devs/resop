<?php

declare(strict_types=1);

namespace App\Domain;

use App\Entity\AvailabilityInterface;

final class AvailabilityDomain
{
    public bool $tick = false;
    public ?AvailabilityInterface $availability;
    public \DateTimeImmutable $date;
    private ?\DateInterval $disabledIntervalFromNow;

    public function __construct(\DateTimeImmutable $date, ?AvailabilityInterface $availability, ?\DateInterval $disabledIntervalFromNow = null)
    {
        $this->date = $date;
        $this->availability = $availability;
        $this->tick = null !== $availability && AvailabilityInterface::STATUS_LOCKED !== $availability->getStatus();
        $this->disabledIntervalFromNow = $disabledIntervalFromNow;
    }

    public function isEditable(): bool
    {
        if (null !== $this->availability && AvailabilityInterface::STATUS_AVAILABLE !== $this->availability->getStatus()) {
            return false;
        }

        $fakeUTCnow = self::getFakeUtcNow();

        if (null !== $this->availability && null !== $this->disabledIntervalFromNow && AvailabilityInterface::STATUS_UNKNOW !== $this->availability->getStatus()) {
            return $this->date > $fakeUTCnow->add($this->disabledIntervalFromNow);
        }

        return $this->date > $fakeUTCnow;
    }

    public static function getFakeUtcNow(): \DateTimeImmutable
    {
        // Dates are stored as UTC even if they are not on this UTC timezone
        // @todo Set the timezone as a parameter
        $trueNow = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));

        return new \DateTimeImmutable($trueNow->format('Y-m-d H:i:s'));
    }
}
