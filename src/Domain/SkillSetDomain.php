<?php

declare(strict_types=1);

namespace App\Domain;

class SkillSetDomain
{
    private array $availableSkillSets;
    private int $importantSkillsLimit;
    private int $importantSkillsToDisplayLimit;

    public function __construct(array $availableSkillSets = [], int $importantSkillsLimit = 0, int $importantSkillsToDisplayLimit = 0)
    {
        // TODO Use objects instead of arrays
        $this->availableSkillSets = $availableSkillSets;
        $this->importantSkillsLimit = $importantSkillsLimit;
        $this->importantSkillsToDisplayLimit = $importantSkillsToDisplayLimit;
    }

    /**
     * Returns an array with saved values as keys and translations as values.
     */
    public function getSkillSet(): array
    {
        return array_map(fn (array $skill) => $skill['label'], $this->availableSkillSets);
    }

    public function getSkillSetKeys(): array
    {
        return array_keys($this->availableSkillSets);
    }

    public function getImportantSkills(): array
    {
        return \array_slice($this->getSkillSetKeys(), 0, $this->importantSkillsLimit);
    }

    public function getSkillsToDisplay(): array
    {
        return \array_slice($this->getSkillSetKeys(), 0, $this->importantSkillsToDisplayLimit);
    }

    public function getDependantSkillsFromSkillSet(array $skillSet): array
    {
        $skills = [];
        foreach ($skillSet as $skill) {
            $skills[] = $skill;
            $skills = array_merge($skills, $this->getDependantSkills($skill));
        }

        return array_values(array_unique($skills));
    }

    private function getDependantSkills(string $skill, array $parsedSkills = []): array
    {
        if (\in_array($skill, $parsedSkills, true)) {
            return [];
        }
        $parsedSkills[] = $skill;

        if (!\in_array($skill, $this->getSkillSetKeys(), true)) {
            return [];
        }

        if (!\array_key_exists('includes', $this->availableSkillSets[$skill])) {
            return [];
        }

        $dependantSkills = [];
        foreach ($this->availableSkillSets[$skill]['includes'] as $include) {
            $dependantSkills[] = $include;
            $dependantSkills = array_merge($dependantSkills, $this->getDependantSkills($include, $parsedSkills));
        }

        return array_values(array_unique($dependantSkills));
    }
}
