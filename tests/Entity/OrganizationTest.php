<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Organization;
use PHPUnit\Framework\TestCase;

final class OrganizationTest extends TestCase
{
    public function testCreateOrganizationWithoutParent(): void
    {
        $organization = new Organization();
        $organization->id = 1;
        $organization->name = 'DT75';

        self::assertSame(1, $organization->id);
        self::assertSame('DT75', $organization->name);
        self::assertSame('DT75', (string) $organization);
        self::assertNull($organization->parent);
    }

    public function testCreateOrganizationWithParent(): void
    {
        $parent = new Organization();
        $parent->id = 1;
        $parent->name = 'DT75';
        $child = new Organization();
        $child->id = 2;
        $child->name = 'UL09';
        $child->parent = $parent;

        self::assertSame(2, $child->id);
        self::assertSame('UL09', $child->name);
        self::assertSame('DT75 - UL09', (string) $child);
        self::assertSame($parent, $child->parent);
    }
}
