<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\ClassNode\ConflictResolver;

use PHPUnit\Framework\TestCase;
use Spiral\Prototype\ClassNode\ConflictResolver\NameEntity;

class EntitiesTest extends TestCase
{
    /**
     * @dataProvider nameProvider
     *
     * @param string $name
     * @param int    $sequence
     * @param string $expected
     */
    public function testName(string $name, int $sequence, string $expected): void
    {
        $this->assertEquals($expected, NameEntity::createWithSequence($name, $sequence)->fullName());
    }

    public function nameProvider(): array
    {
        return [
            ['name', 7, 'name7'],
            ['name', 0, 'name'],
            ['name', -1, 'name'],
            ['name', 1, 'name1'],
        ];
    }
}
