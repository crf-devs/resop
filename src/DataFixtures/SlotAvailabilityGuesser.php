<?php

declare(strict_types=1);

namespace App\DataFixtures;

class SlotAvailabilityGuesser
{
    private const WEEKEND_CHANCES_MODIFIER = 15;
    private const RATE_OF_AVAILABLE_PERCENTAGE = 5;

    private const SLOT_AVAILABLE_DAY = [
        '6', // Saturday
        '7',  // Sunday
    ];

    private int $availableSlotCount = 0;
    private int $availableSlotPercentageWithPrevious = 100;
    private ?\DateTimeImmutable $lastSlot = null;

    public function guessAvailableSlot(\DateTimeImmutable $slot): bool
    {
        $rand = rand(1, 100);
        $chance = $this->getSlotChances($slot);

        if ($this->availableSlotCount > 0) {
            $chance = $this->availableSlotPercentageWithPrevious;

            // Decreasing chances to be available
            $this->availableSlotPercentageWithPrevious -= self::RATE_OF_AVAILABLE_PERCENTAGE;
        }

        if ($rand <= $chance) {
            ++$this->availableSlotCount;
            $this->lastSlot = $slot;

            return true;
        }

        $this->resetGuesser();

        return false;
    }

    public function resetGuesser(int $resetLastSlot = 1): void
    {
        if ($resetLastSlot) {
            $this->lastSlot = null;
        }
        $this->availableSlotCount = 0;
        $this->availableSlotPercentageWithPrevious = 100;
    }

    private function getSlotChances(\DateTimeImmutable $slot): int
    {
        $time = (int) $slot->format('H');

        // Avoid 2 booking same day
        if ($this->lastSlot && ((int) $slot->format('d') <= (int) $this->lastSlot->format('d'))) {
            return -1;
        }

        switch (true) {
            case $time >= 0 && $time < 8:
                $chance = $this->getChancesEarlyMorning();
                break;

            case $time >= 8 && $time < 14:
                $chance = $this->getChancesMorning();
                break;

            case $time >= 14 && $time < 18:
                $chance = $this->getChancesAfternoon();
                break;

            case $time >= 18 && $time < 22:
                $chance = $this->getChancesEvening();
                break;

            default:
                return -1;
        }

        // If slot is during weekend or out of work hours there is more chance of availability
        if (\in_array($slot->format('N'), self::SLOT_AVAILABLE_DAY, true)) {
            $chance += self::WEEKEND_CHANCES_MODIFIER;
        }

        return $chance;
    }

    private function getChancesEarlyMorning(): int
    {
        // 5% chances to get a booked in the early morning
        return 10;
    }

    private function getChancesMorning(): int
    {
        // 10% chances to get a booked in the morning
        return 20;
    }

    private function getChancesAfternoon(): int
    {
        // 10% chances to get a booked in the afternoon
        return 25;
    }

    private function getChancesEvening(): int
    {
        // 10% chances to get a booked in the evening
        return 35;
    }
}
