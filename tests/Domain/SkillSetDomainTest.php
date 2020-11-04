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
                'skill2' => ['label' => 'Skill 2', 'includes' => ['skill3', 'skill5']],
                'skill3' => ['label' => 'Skill 3', 'includes' => ['skill4', 'skill5']],
                'skill4' => ['label' => 'Skill 4', 'includes' => ['skill5']],
                'skill5' => ['label' => 'Skill 5', 'includes' => []],
                'skill6' => ['label' => 'Skill 6'],
            ],
            3,
            ['skill1', 'skill5']
        );
    }

    public function testGetSkillSet(): void
    {
        self::assertSame(
            $this->skillSetDomain->getSkillSet(),
            [
                'skill1' => 'Skill 1',
                'skill2' => 'Skill 2',
                'skill3' => 'Skill 3',
                'skill4' => 'Skill 4',
                'skill5' => 'Skill 5',
                'skill6' => 'Skill 6',
            ]
        );
    }

    public function testGetSkillSetKeys(): void
    {
        self::assertSame(
            $this->skillSetDomain->getSkillSetKeys(),
            ['skill1', 'skill2', 'skill3', 'skill4', 'skill5', 'skill6']
        );
    }

    public function testGetImportantSkills(): void
    {
        self::assertSame(
            $this->skillSetDomain->getImportantSkills(),
            ['skill1', 'skill2', 'skill3']
        );
    }

    public function testGetSkillsToDisplay(): void
    {
        self::assertSame(
            $this->skillSetDomain->getSkillsToDisplay(),
            ['skill1', 'skill5']
        );
    }

    /** @dataProvider includedSkillsFromSkillSetProvider */
    public function testGetIncludedSkillsFromSkillSet(array $skills, array $expectedSkills): void
    {
        self::assertSame($expectedSkills, $this->skillSetDomain->getIncludedSkillsFromSkillSet($skills));
    }

    public function includedSkillsFromSkillSetProvider(): array
    {
        return [
            'no_skills' => [
                'skill' => [],
                'expectedChildren' => [],
            ],
            'nonexistent_skill' => [
                'skill' => ['foo'],
                'expectedChildren' => ['foo'],
            ],
            'no_child' => [
                'skill' => ['skill6'],
                'expectedChildren' => ['skill6'],
            ],
            'one_child' => [
                'skill' => ['skill4'],
                'expectedChildren' => ['skill4', 'skill5'],
            ],
            'multiple_children' => [
                'skill' => ['skill3'],
                'expectedChildren' => ['skill3', 'skill4', 'skill5'],
            ],
            'multiple_children_recursive' => [
                'skill' => ['skill2'],
                'expectedChildren' => ['skill2', 'skill3', 'skill4', 'skill5'],
            ],
            'multiple_children_recursive_2' => [
                'skill' => ['skill2', 'skill6'],
                'expectedChildren' => ['skill2', 'skill3', 'skill4', 'skill5', 'skill6'],
            ],
            'multiple_children_with_duplicates' => [
                'skill' => ['skill1'],
                'expectedChildren' => ['skill1', 'skill2', 'skill3', 'skill4', 'skill5'],
            ],
            'multiple_children_with_duplicates_2' => [
                'skill' => ['skill1', 'skill2', 'skill6'],
                'expectedChildren' => ['skill1', 'skill2', 'skill3', 'skill4', 'skill5', 'skill6'],
            ],
        ];
    }

    /** @dataProvider getIncludedSkillsFromSkillSetPreventsInfiniteLoopProvider */
    public function testGetIncludedSkillsFromSkillSetPreventsInfiniteLoop(array $skillSetWithInfiniteLoop, array $expectedSkillSet): void
    {
        $skillSetDomain = new SkillSetDomain($skillSetWithInfiniteLoop);

        self::assertSame($expectedSkillSet, $skillSetDomain->getIncludedSkillsFromSkillSet(['skill1']));
    }

    public function getIncludedSkillsFromSkillSetPreventsInfiniteLoopProvider(): array
    {
        return [
            'loopWithinMainSkill' => [
                'recursionWithin' => [
                    'skill1' => ['includes' => ['skill2']],
                    'skill2' => ['includes' => ['skill1']],
                ],
                'expectedSkillSet' => ['skill1', 'skill2'],
            ],
            'loopWithoutMainSkill' => [
                'skillSetWithInfiniteLoop' => [
                    'skill1' => ['includes' => ['skill2']],
                    'skill2' => ['includes' => ['skill3']],
                    'skill3' => ['includes' => ['skill2']],
                ],
                'expectedSkillSet' => ['skill1', 'skill2', 'skill3'],
            ],
        ];
    }

    /** @dataProvider filterIncludedSkillsProvider */
    public function testFilterIncludedSkills(array $skills, array $expectedDisplayableSkills): void
    {
        self::assertSame($expectedDisplayableSkills, $this->skillSetDomain->filterIncludedSkills($skills));
    }

    public function filterIncludedSkillsProvider(): array
    {
        return [
            'no_skills' => [
                'skills' => [],
                'expectedDisplayableSkills' => [],
            ],
            'nonexistent_skill' => [
                'skills' => ['foo'],
                'expectedDisplayableSkills' => [],
            ],
            'one_main_skill' => [
                'skills' => ['skill1'],
                'expectedDisplayableSkills' => ['skill1'],
            ],
            'one_dependant_skill' => [
                'skills' => ['skill2'],
                'expectedDisplayableSkills' => ['skill2'],
            ],
            'two_main_skills' => [
                'skills' => ['skill1', 'skill6'],
                'expectedDisplayableSkills' => ['skill1', 'skill6'],
            ],
            'child_and_parent' => [
                'skills' => ['skill1', 'skill2'],
                'expectedDisplayableSkills' => ['skill1'],
            ],
            'parent_not_main_skill' => [
                'skills' => ['skill4', 'skill5'],
                'expectedDisplayableSkills' => ['skill4'],
            ],
            'not_direct_parent' => [
                'skills' => ['skill1', 'skill5'],
                'expectedDisplayableSkills' => ['skill1'],
            ],
            'all_skills' => [
                'skills' => ['skill1', 'skill2', 'skill3', 'skill4', 'skill5', 'skill6'],
                'expectedDisplayableSkills' => ['skill1', 'skill6'],
            ],
        ];
    }

    public function testFilterIncludedSkillsPreventsInfiniteLoop(): void
    {
        $skillSetDomain = new SkillSetDomain(
            [
                'skill1' => ['includes' => ['skill2']],
                'skill2' => ['includes' => ['skill3']],
                'skill3' => ['includes' => ['skill2']],
            ]
        );

        self::assertSame(
            ['skill1'],
            $skillSetDomain->filterIncludedSkills(['skill1', 'skill2', 'skill3'])
        );
    }
}
