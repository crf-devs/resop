<?php

declare(strict_types=1);

namespace App\Tests\Twig\Cache;

use App\Twig\Cache\RequestGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class RequestGeneratorTest extends TestCase
{
    /**
     * @dataProvider getFilters
     */
    public function testItGeneratesAValidKeyFromArray(string $expected, array $data): void
    {
        $this->assertEquals($expected, (new RequestGenerator())->generateKey($data));
    }

    public function getFilters(): array
    {
        return [
            ['3a3160c3d13a2b6e9a56089f00c0743bd55166fb', ['foo']],
            ['289df0705aad811a4f04744ea205da25cad15371', ['bar']],
        ];
    }
}
