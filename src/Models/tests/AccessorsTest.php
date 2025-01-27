<?php

declare(strict_types=1);

namespace Spiral\Tests\Models;

use PHPUnit\Framework\TestCase;
use Spiral\Models\Exception\EntityException;
use Spiral\Models\Reflection\ReflectionEntity;

class AccessorsTest extends TestCase
{
    public function testAccessed(): void
    {
        $e = new AccessedEntity();
        $e->name = 'antony';
        self::assertSame('ANTONY', (string) $e->name);

        $e->setFields(['name' => 'bob']);
        self::assertSame('BOB', (string) $e->name);

        self::assertSame([
            'name' => 'BOB',
        ], $e->getValue());

        self::assertSame([
            'name' => 'BOB',
        ], $e->jsonSerialize());

        self::assertEquals([
            'name' => new NameValue('bob'),
        ], $e->getFields());

        $e->name = new NameValue('mike');

        self::assertEquals([
            'name' => new NameValue('mike'),
        ], $e->getFields());
    }

    public function testGetAccessor(): void
    {
        $e = new AccessedEntity();
        self::assertSame('', (string) $e->name);
        self::assertInstanceOf(NameValue::class, $e->name);

        self::assertEquals([
            'name' => new NameValue(null),
        ], $e->getFields());

        $e->setFields();
    }

    public function testReflection(): void
    {
        $s = new ReflectionEntity(AccessedEntity::class);
        self::assertSame([
            'name' => NameValue::class,
        ], $s->getAccessors());
    }

    public function testException(): void
    {
        $this->expectException(EntityException::class);

        $e = new BadAccessedEntity();
        $e->name = 'xx';
    }
}
