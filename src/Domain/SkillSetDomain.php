<?php

declare(strict_types=1);

namespace App\Domain;

class SkillSetDomain
{
    private array $availableSkillSets;
    private int $importantSkillsLimit;
    private array $importantSkillsToDisplay;

    public function __construct(array $availableSkillSets = [], int $importantSkillsLimit = 0, array $importantSkillsToDisplay = [])
    {
        // TODO Use objects instead of arrays
        $this->availableSkillSets = $availableSkillSets;
        $this->importantSkillsLimit = $importantSkillsLimit;
        $this->importantSkillsToDisplay = $importantSkillsToDisplay;
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
        return array_values(array_intersect($this->getSkillSetKeys(), $this->importantSkillsToDisplay));
    }

    public function getIncludedSkillsFromSkillSet(array $skillSet): array
    {
        $skills = [];
        foreach ($skillSet as $skill) {
            $skills[] = $skill;
            $skills = array_merge($skills, $this->getDependantSkills($skill));
        }

        return array_values(array_unique($skills));
    }

    public function filterIncludedSkills(array $skillSet): array
    {
        return array_values(
            array_filter($skillSet, fn ($skill) => $this->shouldDisplaySkillInPlanning($skill, $skillSet))
        );
    }

    private function getDependantSkills(string $skill, array $parsedSkills = []): array
    {
        // prevents infinite loop
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

    private function shouldDisplaySkillInPlanning(string $skill, array $skillSet): bool
    {
        return \in_array($skill, $this->getSkillSetKeys(), true)
            && 0 === \count(array_intersect($skillSet, $this->getParents($skill)));
    }

    private function getParents(string $skill, array $parsedSkills = []): array
    {
        // prevents infinite loop
        if (\in_array($skill, $parsedSkills, true)) {
            return [];
        }
        $parsedSkills[] = $skill;

        if (!\in_array($skill, $this->getSkillSetKeys(), true)) {
            return [];
        }

        $parents = [];
        foreach ($this->availableSkillSets as $availableSkill => $skillDetails) {
            if (\in_array($skill, $skillDetails['includes'] ?? [], true)) {
                $parents[] = $availableSkill;
                $parents = array_merge($parents, $this->getParents($availableSkill, $parsedSkills));
            }
        }

        return $parents;
    }
}
