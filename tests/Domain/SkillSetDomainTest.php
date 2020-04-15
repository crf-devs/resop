<?php

declare(strict_types=1);

namespace App\Tests\Domain;

use App\Domain\SkillSetDomain;
use PHPUnit\Framework\TestCase;

final class SkillSetDomainTest extends TestCase
{
    private SkillSetDomain $skillSetDomain;

    public function setUp(): void
    {
        $this->skillSetDomain = new SkillSetDomain(
            [
                'skill1' => ['label' => 'Skill 1', 'includes' => ['skill2', 'skill3']],
                'skill2' => ['label' => 'Skill 2', 'includes' => ['skill3']],
                'skill3' => ['label' => 'Skill 3', 'includes' => ['skill4']],
                'skill4' => ['label' => 'Skill 4', 'includes' => []],
                'skill5' => ['label' => 'Skill 5'],
            ],
            3,
            4
        );
    }

    public function testGetSkillSet(): void
    {
        $this->assertSame(
            $this->skillSetDomain->getSkillSet(),
            [
                'skill1' => 'Skill 1',
                'skill2' => 'Skill 2',
                'skill3' => 'Skill 3',
                'skill4' => 'Skill 4',
                'skill5' => 'Skill 5',
            ]
        );
    }

    public function testGetSkillSetKeys(): void
    {
        $this->assertSame(
            $this->skillSetDomain->getSkillSetKeys(),
            ['skill1', 'skill2', 'skill3', 'skill4', 'skill5']
        );
    }

    public function testGetImportantSkills(): void
    {
        $this->assertSame(
            $this->skillSetDomain->getImportantSkills(),
            ['skill1', 'skill2', 'skill3']
        );
    }

    public function testGetSkillsToDisplay(): void
    {
        $this->assertSame(
            $this->skillSetDomain->getSkillsToDisplay(),
            ['skill1', 'skill2', 'skill3', 'skill4']
        );
    }
}
