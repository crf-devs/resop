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
        $this->importantSkillsLimit = (int) $importantSkillsLimit;
        $this->importantSkillsToDisplayLimit = (int) $importantSkillsToDisplayLimit;
    }

    /**
     * Returns an array with saved savlues as keys and translations as values.
     */
    public function getSkillSet(): array
    {
        return $this->availableSkillSets;
    }

    public function getSkillSetKeys(): array
    {
        return array_keys($this->availableSkillSets);
    }

    public function getImportantSkills(): array
    {
        return array_slice($this->getSkillSetKeys(), 0, $this->importantSkillsLimit);
    }

    public function getSkillsToDisplay(): array
    {
        return array_slice($this->getSkillSetKeys(), 0, $this->importantSkillsToDisplayLimit);
    }
}
