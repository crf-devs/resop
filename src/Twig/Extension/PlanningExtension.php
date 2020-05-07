<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Domain\DatePeriodCalculator;
use App\Domain\PlanningDomain;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class PlanningExtension extends AbstractExtension
{
    private PlanningDomain $planningDomain;
    private string $slotInterval;

    public function __construct(PlanningDomain $planningDomain, string $slotInterval)
    {
        $this->planningDomain = $planningDomain;
        $this->slotInterval = $slotInterval;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('renderPlanningTable', [$this, 'renderTable']),
            new TwigFunction('getAvailabilities', [$this, 'getAvailabilities']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('timeSlot', [$this, 'getTimeSlot']),
        ];
    }

    public function getTimeSlot(\DateTimeInterface $time): string
    {
        return 0 === \DateInterval::createFromDateString($this->slotInterval)->i ? $time->format('H') : $time->format('H:i');
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
}
