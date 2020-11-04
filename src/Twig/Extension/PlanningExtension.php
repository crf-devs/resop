<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Domain\AvailabilityDomain;
use App\Domain\DatePeriodCalculator;
use App\Domain\PlanningDomain;
use App\Entity\Mission;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class PlanningExtension extends AbstractExtension
{
    private PlanningDomain $planningDomain;
    private string $slotInterval;
    private TranslatorInterface $translator;

    public function __construct(PlanningDomain $planningDomain, string $slotInterval, TranslatorInterface $translator)
    {
        $this->planningDomain = $planningDomain;
        $this->slotInterval = $slotInterval;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('renderPlanningTable', [$this, 'renderTable']),
            new TwigFunction('getAvailabilities', [$this, 'getAvailabilities']),
            new TwigFunction('getFakeNow', [$this, 'getFakeNow']),
            new TwigFunction('calendarTimeSlot', [$this, 'calendarTimeSlot']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('timeSlot', [$this, 'getTimeSlot']),
            new TwigFilter('filterMissionsByDate', [$this, 'filterMissionsByDate']),
        ];
    }

    public function isFullHourInterval(): bool
    {
        return 0 === DatePeriodCalculator::intervalToSeconds(\DateInterval::createFromDateString($this->slotInterval)) % 3600;
    }

    public function getTimeSlot(\DateTimeInterface $time): string
    {
        return 0 === \DateInterval::createFromDateString($this->slotInterval)->i ? $time->format('H') : $time->format('H:i');
    }

    public function calendarTimeSlot(\DateTimeImmutable $start, \DateTimeImmutable $end): string
    {
        $hourFormat = static fn (\DateTimeImmutable $date) => '0' === $date->format('G') ? '00' : $date->format('G');

        if ($this->isFullHourInterval()) {
            if ('00' === $start->format('H') && '00' === $end->format('H')) {
                return $this->translator->trans('calendar.fullDay');
            }

            return sprintf('%sh - %sh', $hourFormat($start), $hourFormat($end));
        }

        $end = $end->sub(new \DateInterval('PT1M'));

        return sprintf('%sh - %sh', $start->format('H:i'), $end->format('H:i'));
    }

    public function getAvailabilities(DatePeriodCalculator $periodCalculator, array $filters): array
    {
        return $this->planningDomain->generateAvailabilities($filters, $periodCalculator->getPeriod());
    }

    public function renderTable(array $availabilities, bool $displayActions): string
    {
        $res = '';
        foreach ($availabilities as $slot) {
            $res .= sprintf(
                '<td class="slot-box %s" data-status="%s" %s data-day="%s" data-from="%s" data-to="%s">%s</td>',
                $slot['status'],
                $slot['status'],
                empty($slot['comment']) ? '' : sprintf('data-comment="%s"', htmlspecialchars($slot['comment'])),
                $slot['fromDay'],
                $slot['fromDate'],
                $slot['toDate'],
                $displayActions ? '<input type="checkbox">' : ''
            );
        }

        return $res;
    }

    public function getFakeNow(): \DateTimeImmutable
    {
        return AvailabilityDomain::getFakeUtcNow();
    }

    public function filterMissionsByDate(array $missions, \DateTimeImmutable $start, \DateTimeImmutable $end): array
    {
        return array_filter($missions, static function (Mission $mission) use ($start, $end) {
            return
                (null === $mission->startTime && null === $mission->endTime) ||
                ($mission->startTime >= $start && $mission->endTime <= $end) ||
                ($mission->startTime <= $start && $mission->endTime >= $start) ||
                ($mission->startTime <= $end && $mission->endTime >= $end);
        });
    }
}
