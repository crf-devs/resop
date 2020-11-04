<?php

declare(strict_types=1);

namespace App\Tests\Domain;

use App\Domain\AvailabilitiesDomain;
use PHPUnit\Framework\TestCase;

final class AvailabilitiesDomainTest extends TestCase
{
    /**
     * @dataProvider getInvalidSlotIntervals
     */
    public function testSlotIntervalException(string $slotInterval, string $message): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        new AvailabilitiesDomain([], $slotInterval);
    }

    public function getInvalidSlotIntervals(): array
    {
        return [
            ['+2 hours 30 minutes', 'Invalid slot interval: unable to set a complete day with "+2 hours 30 minutes".'],
            ['+42 minutes', 'Invalid slot interval: unable to set a complete day with "+42 minutes".'],
            ['+5 hours', 'Invalid slot interval: unable to set a complete day with "+5 hours".'],
        ];
    }

    /**
     * @dataProvider getValidSlotIntervals
     */
    public function testNewAvailabilitiesDomain(string $slotInterval): void
    {
        self::assertInstanceOf(AvailabilitiesDomain::class, new AvailabilitiesDomain([], $slotInterval));
    }

    public function getValidSlotIntervals(): array
    {
        return [
            ['+1 hour 30 minutes'],
            ['+2 hours'],
            ['+3 hours'],
            ['+4 hours'],
            ['+6 hours'],
        ];
    }
}
