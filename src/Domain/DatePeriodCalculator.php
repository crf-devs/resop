<?php

declare(strict_types=1);

namespace App\Domain;

class DatePeriodCalculator
{
    private \DatePeriod $period;

    public function __construct(\DateTimeInterface $from, \DateInterval $interval, \DateTimeInterface $to)
    {
        $this->period = new \DatePeriod($from, $interval, $to);
    }

    public function getPeriod(): \DatePeriod
    {
        return $this->period;
    }

    public function getDays(): array
    {
        /** @var \DateTimeInterface[] $period */
        $period = $this->getPeriod();

        $days = [];
        foreach ($period as $value) {
            $dayStr = $value->format('Y-m-d');

            if (!isset($days[$dayStr])) {
                $days[$dayStr] = [
                    'date' => new \DateTimeImmutable(sprintf('%s 0:0:0', $dayStr)),
                    'slots' => 0,
                ];
            }

            ++$days[$dayStr]['slots'];
        }

        return $days;
    }

    public function getFrom(): \DateTimeInterface
    {
        return $this->period->getStartDate();
    }

    public function getTo(): \DateTimeInterface
    {
        return $this->period->getEndDate();
    }
}
