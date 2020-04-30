<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Domain\SkillSetDomain;
use App\Entity\User;
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
            new TwigFilter('sortBySkills', [$this, 'sortBySkills']),
        ];
    }

    public function formatBadge(string $skill): string
    {
        $importantSkills = $this->skillSetDomain->getImportantSkills();

        return sprintf('<span class="badge badge-%s mr-1">%s</span>', \in_array($skill, $importantSkills, true) ? 'primary' : 'secondary', $skill);
    }

    /**
     * @param array|\IteratorAggregate $users
     */
    public function sortBySkills($users): array
    {
        $skillSet = $this->skillSetDomain->getSkillSetKeys();

        if ($users instanceof \IteratorAggregate) {
            $users = iterator_to_array($users->getIterator());
        }

        usort($users, static function (User $a, User $b) use ($skillSet) {
            $sortedSkillsA = array_values(array_intersect($skillSet, $a->skillSet));
            $sortedSkillsB = array_values(array_intersect($skillSet, $b->skillSet));

            $bestSkillPosA = array_search($sortedSkillsA[0] ?? null, $skillSet, true);
            $bestSkillPosB = array_search($sortedSkillsB[0] ?? null, $skillSet, true);

            if ($bestSkillPosA === $bestSkillPosB) {
                return 0;
            }

            if ($bestSkillPosA > $bestSkillPosB) {
                return 1;
            }

            return -1;
        });

        return $users;
    }
}
