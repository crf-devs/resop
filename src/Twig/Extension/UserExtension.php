<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Domain\SkillSetDomain;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class UserExtension extends AbstractExtension
{
    private SkillSetDomain $skillSetDomain;

    public function __construct(SkillSetDomain $skillSetDomain)
    {
        $this->skillSetDomain = $skillSetDomain;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('skillBadge', [$this, 'formatBadge'], ['is_safe' => ['html']]),
        ];
    }

    public function formatBadge(string $skill): string
    {
        $importantSkills = $this->skillSetDomain->getImportantSkills();

        return sprintf('<span class="badge badge-%s mr-1">%s</span>', \in_array($skill, $importantSkills, true) ? 'primary' : 'secondary', $skill);
    }
}
