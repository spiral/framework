<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\ClassNode\ConflictResolver;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Prototype\ClassNode\ConflictResolver\NameEntity;

class EntitiesTest extends TestCase
{
    #[DataProvider('nameProvider')]
    public function testName(string $name, int $sequence, string $expected): void
    {
        $this->assertEquals($expected, NameEntity::createWithSequence($name, $sequence)->fullName());
    }

    public static function nameProvider(): \Traversable
    {
        yield ['name', 7, 'name7'];
        yield ['name', 0, 'name'];
        yield ['name', -1, 'name'];
        yield ['name', 1, 'name1'];
    }
}
