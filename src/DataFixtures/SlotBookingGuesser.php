<?php

declare(strict_types=1);

namespace App\DataFixtures;

class SlotBookingGuesser
{
    private const RATE_OF_BOOKED_PERCENTAGE = 10;
    private const NUMBER_BOOKED_SLOT_CONSECUTIVE = 8;

    private int $bookedSlotPercentageWithPrevious = 100;
    private int $bookedSlotCount = 0;
    private ?\DateTimeImmutable $lastSlot = null;

    public function guessBookedSlot(\DateTimeImmutable $slot): bool
    {
        $rand = random_int(1, 100);

        if ($this->isPreviousSlotBooked()) {
            if ($rand <= $this->bookedSlotPercentageWithPrevious) {
                //Decreasing chances of being booked
                $this->bookedSlotPercentageWithPrevious -= self::RATE_OF_BOOKED_PERCENTAGE;
                $this->lastSlot = $slot;

                return true;
            }

            $this->resetGuesser(0);

            return false;
        }

        $chances = $this->getSlotChances($slot);

        if ($rand <= $chances) {
            $this->lastSlot = $slot;
            ++$this->bookedSlotCount;

            return true;
        }

        return false;
    }

    public function resetGuesser(int $resetLastBookedSlot = 1): void
    {
        if ($resetLastBookedSlot) {
            $this->lastSlot = null;
        }
        $this->bookedSlotCount = 0;
        $this->bookedSlotPercentageWithPrevious = 100;
    }

    private function isPreviousSlotBooked(): bool
    {
        if (0 === $this->bookedSlotCount) {
            return false;
        }

        if ($this->bookedSlotCount > self::NUMBER_BOOKED_SLOT_CONSECUTIVE) {
            $this->resetGuesser(0);

            return false;
        }

        // Avoid changing day booking (let's not make them work from 20h to 4h in the morning !)
        if ($this->lastSlot && ((int) $this->lastSlot->format('H') >= 22)) {
            $this->resetGuesser();

            return false;
        }

        ++$this->bookedSlotCount;

        return true;
    }

    private function getSlotChances(\DateTimeImmutable $dateTime): int
    {
        $time = (int) $dateTime->format('H');

        // Avoid 2 bookings same day
        if ($this->lastSlot && ((int) $dateTime->format('d') <= (int) $this->lastSlot->format('d'))) {
            return -1;
        }

        switch (true) {
            case $time >= 0 && $time < 8:
                return $this->getChancesEarlyMorning();

            case $time >= 8 && $time < 14:
                return $this->getChancesMorning();

            case $time >= 14 && $time < 18:
                return $this->getChancesAfternoon();

            case $time >= 18 && $time < 22:
                return $this->getChancesEvening();

            default:
                return -1;
        }
    }

    private function getChancesEarlyMorning(): int
    {
        // 5% chances to get a booked in the early morning
        return 5;
    }

    private function getChancesMorning(): int
    {
        // 10% chances to get a booked in the morning
        return 10;
    }

    private function getChancesAfternoon(): int
    {
        // 10% chances to get a booked in the afternoon
        return 10;
    }

    private function getChancesEvening(): int
    {
        // 10% chances to get a booked in the evening
        return 20;
    }
}
