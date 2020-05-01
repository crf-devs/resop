<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Domain\DatePeriodCalculator;
use App\Domain\SkillSetDomain;
use App\Entity\User;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class UserExtension extends AbstractExtension
{
    private bool $hoursFull;
    private SkillSetDomain $skillSetDomain;

    public function __construct(SkillSetDomain $skillSetDomain, string $slotInterval)
    {
        $this->skillSetDomain = $skillSetDomain;

        $this->hoursFull = 0 === DatePeriodCalculator::intervalToSeconds(\DateInterval::createFromDateString($slotInterval)) % 3600;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('calendarCustomHours', [$this, 'calendarCustomHours']),
            new TwigFilter('calendarDayClass', [$this, 'calendarDayClass']),
            new TwigFilter('skillBadge', [$this, 'formatBadge'], ['is_safe' => ['html']]),
            new TwigFilter('userBadges', [$this, 'userBadges'], ['is_safe' => ['html']]),
            new TwigFilter('sortBySkills', [$this, 'sortBySkills']),
            new TwigFilter('filterSkillsToDisplay', [$this, 'filterSkillsToDisplay']),
            new TwigFilter('filterInludedSkills', [$this, 'filterInludedSkills']),
        ];
    }

    public function filterSkillsToDisplay(array $skills): array
    {
        return array_values(array_intersect($skills, $this->skillSetDomain->getSkillsToDisplay()));
    }

    public function filterInludedSkills(array $skills): array
    {
        return $this->skillSetDomain->filterIncludedSkills($skills);
    }

    public function userBadges(User $user): string
    {
        return implode('', array_map(
            fn (string $skill) => $this->formatBadge($skill),
            $this->filterInludedSkills($user->skillSet)
        ));
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

    public function calendarCustomHours(\DateTimeImmutable $dateTime, bool $isEndTime = false): string
    {
        if (true === $this->hoursFull) {
            return $dateTime->format('H').'h';
        }

        if (true === $isEndTime) {
            $dateTime = $dateTime->sub(new \DateInterval('PT1M'));
        }

        return $dateTime->format('H:i');
    }

    public function calendarDayClass(\DateTimeInterface $dateTime): string
    {
        $today = (new \DateTime('today'))->format('Ymd');
        $currentDate = $dateTime->format('Ymd');

        if ($today === $currentDate) {
            return 'current';
        }

        if ($today < $currentDate) {
            return 'incoming';
        }

        return 'previous';
    }
}
