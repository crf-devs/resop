<?php

declare(strict_types=1);

namespace App\Tests\Domain;

use App\Domain\DatePeriodCalculator;
use PHPUnit\Framework\TestCase;

final class DatePeriodCalculatorTest extends TestCase
{
    private DatePeriodCalculator $datePeriodCalculator;

    public function setUp(): void
    {
        $this->datePeriodCalculator = new DatePeriodCalculator(
            new \DateTimeImmutable('2020-04-22 20:00:00'),
            new \DateInterval('PT2H'),
            new \DateTimeImmutable('2020-04-23 02:00:00'),
        );
    }

    public function testGetPeriod(): void
    {
        $period = $this->datePeriodCalculator->getPeriod();

        $this->assertSame('2020-04-22T20:00:00+00:00', $period->start->format(\DateTimeInterface::ATOM));
        $this->assertSame('2:0:0', $period->interval->format('%h:%i:%s'));
        $this->assertSame('2020-04-23T02:00:00+00:00', $period->end->format(\DateTimeInterface::ATOM));
    }

    public function testGetInterval(): void
    {
        $this->assertSame('2:0:0', $this->datePeriodCalculator->getInterval()->format('%h:%i:%s'));
    }

    public function testGetSlots(): void
    {
        $this->assertEquals([
            new \DateTimeImmutable('2020-04-22 20:00:00'),
            new \DateTimeImmutable('2020-04-22 22:00:00'),
            new \DateTimeImmutable('2020-04-23 00:00:00'),
        ], $this->datePeriodCalculator->getSlots());
    }

    public function testGetDays(): void
    {
        $this->assertEquals([
            '2020-04-22' => [
                'date' => new \DateTimeImmutable('2020-04-22 00:00:00'),
                'slots' => 2,
            ],
            '2020-04-23' => [
                'date' => new \DateTimeImmutable('2020-04-23 00:00:00'),
                'slots' => 1,
            ],
        ], $this->datePeriodCalculator->getDays());
    }

    public function testGetFrom(): void
    {
        $this->assertSame('2020-04-22T20:00:00+00:00', $this->datePeriodCalculator->getFrom()->format(\DateTimeInterface::ATOM));
    }

    public function testGetTo(): void
    {
        $this->assertSame('2020-04-23T02:00:00+00:00', $this->datePeriodCalculator->getTo()->format(\DateTimeInterface::ATOM));
    }

    public function testCreateRoundedToDay(): void
    {
        $created = DatePeriodCalculator::createRoundedToDay(
            new \DateTimeImmutable('2020-04-22 20:00:00'),
            new \DateInterval('PT2H'),
            new \DateTimeImmutable('2020-04-23 02:00:00'),
        );

        $period = $created->getPeriod();
        $this->assertSame('2020-04-22T00:00:00+00:00', $period->start->format(\DateTimeInterface::ATOM));
        $this->assertSame('2020-04-24T00:00:00+00:00', $period->end->format(\DateTimeInterface::ATOM));
    }

    public function testIntervalToSeconds(): void
    {
        $this->assertSame(7200, DatePeriodCalculator::intervalToSeconds(new \DateInterval('PT2H')));
        $this->assertSame(1892527680, DatePeriodCalculator::intervalToSeconds(new \DateInterval('P2Y4DT6H8M')));
    }

    /** @dataProvider roundToDailyIntervalProvider */
    public function testRoundToDailyInterval(string $expectedDate, string $date, string $interval, bool $floor = true): void
    {
        $this->assertEquals(
            new \DateTimeImmutable($expectedDate),
            DatePeriodCalculator::roundToDailyInterval(
                new \DateTimeImmutable($date),
                new \DateInterval($interval),
                $floor
            )
        );
    }

    public function roundToDailyIntervalProvider(): array
    {
        return [
            'midnight' => [
                'expectedDate' => '2020-01-10 00:00:00',
                'date' => '2020-01-10 00:00:00',
                'interval' => 'PT2H',
                'floor' => true,
            ],
            'midnight ceil' => [
                'expectedDate' => '2020-01-10 00:00:00',
                'date' => '2020-01-10 00:00:00',
                'interval' => 'PT2H',
                'floor' => false,
            ],
            '1h too late' => [
                'expectedDate' => '2020-01-10 00:00:00',
                'date' => '2020-01-10 01:30:00',
                'interval' => 'PT2H',
                'floor' => true,
            ],
            '1h too late ceil' => [
                'expectedDate' => '2020-01-10 02:00:00',
                'date' => '2020-01-10 01:30:00',
                'interval' => 'PT2H',
                'floor' => false,
            ],
            'on a slot' => [
                'expectedDate' => '2020-01-10 02:00:00',
                'date' => '2020-01-10 02:00:00',
                'interval' => 'PT2H',
                'floor' => true,
            ],
            'on a slot ceil' => [
                'expectedDate' => '2020-01-10 02:00:00',
                'date' => '2020-01-10 02:00:00',
                'interval' => 'PT2H',
                'floor' => true,
            ],
            'between slots with minutes' => [
                'expectedDate' => '2020-01-10 02:30:00',
                'date' => '2020-01-10 02:45:00',
                'interval' => 'PT2H30M',
                'floor' => true,
            ],
            'between slots with minutes and seconds ceil' => [
                'expectedDate' => '2020-01-10 02:30:45',
                'date' => '2020-01-10 01:45:00',
                'interval' => 'PT2H30M45S',
                'floor' => false,
            ],
        ];
    }
}
