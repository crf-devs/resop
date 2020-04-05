<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Domain\DatePeriodCalculator;
use App\Domain\PlanningDomain;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class PlanningExtension extends AbstractExtension
{
    private PlanningDomain $planningDomain;

    public function __construct(PlanningDomain $planningDomain)
    {
        $this->planningDomain = $planningDomain;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getAvailabilities', [$this, 'getAvailabilities']),
        ];
    }

    public function getAvailabilities(DatePeriodCalculator $periodCalculator, array $filters): array
    {
        return $this->planningDomain->generateAvailabilities($filters, $periodCalculator->getPeriod());
    }
}
