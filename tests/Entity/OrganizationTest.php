<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Organization;
use PHPUnit\Framework\TestCase;

final class OrganizationTest extends TestCase
{
    public function testCreateOrganizationWithoutParent(): void
    {
        $organization = new Organization(1, 'DT75');

        $this->assertSame(1, $organization->id);
        $this->assertSame('DT75', $organization->name);
        $this->assertSame('DT75', (string) $organization);
        $this->assertNull($organization->parent);
    }

    public function testCreateOrganizationWithParent(): void
    {
        $parent = new Organization(1, 'DT75');
        $child = new Organization(2, 'UL09', $parent);

        $this->assertSame(2, $child->id);
        $this->assertSame('UL09', $child->name);
        $this->assertSame('DT75 - UL09', (string) $child);
        $this->assertSame($parent, $child->parent);
    }
}
