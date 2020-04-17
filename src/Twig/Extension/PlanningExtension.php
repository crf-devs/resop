<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Domain\DatePeriodCalculator;
use App\Domain\PlanningDomain;
use App\Domain\SkillSetDomain;
use App\Entity\User;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class PlanningExtension extends AbstractExtension
{
    private PlanningDomain $planningDomain;
    private SkillSetDomain $skillSetDomain;

    public function __construct(PlanningDomain $planningDomain, SkillSetDomain $skillSetDomain)
    {
        $this->planningDomain = $planningDomain;
        $this->skillSetDomain = $skillSetDomain;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getAvailabilities', [$this, 'getAvailabilities']),
            new TwigFunction('getDisplayableSkillsInPlanning', [$this, 'getDisplayableSkills']),
        ];
    }

    public function getAvailabilities(DatePeriodCalculator $periodCalculator, array $filters): array
    {
        return $this->planningDomain->generateAvailabilities($filters, $periodCalculator->getPeriod());
    }

    public function getDisplayableSkills(User $user): array
    {
        return $this->skillSetDomain->filterIncludedSkills($user->skillSet);
    }
}
