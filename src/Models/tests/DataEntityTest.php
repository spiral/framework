<?php

declare(strict_types=1);

namespace Spiral\Tests\Models;

use PHPUnit\Framework\TestCase;
use Spiral\Models\DataEntity;

class DataEntityTest extends TestCase
{
    public function testSetter(): void
    {
        $entity = new DataEntity();
        $entity->setField('abc', 123);
        self::assertEquals(123, $entity->getField('abc'));

        self::assertTrue($entity->hasField('abc'));
        self::assertFalse($entity->hasField('bce'));
    }

    public function testMagicProperties(): void
    {
        $entity = new DataEntity();
        $entity->abc = 123;
        self::assertSame(123, $entity->abc);

        self::assertTrue(isset($entity->abc));
    }

    public function testPackingSimple(): void
    {
        $entity = new DataEntity(['a' => 'b', 'c' => 10]);
        self::assertSame(['a' => 'b', 'c' => 10], $entity->getValue());
    }

    public function testSerialize(): void
    {
        $data = ['a' => 123, 'b' => null, 'c' => 'test'];

        $entity = new DataEntity($data);
        self::assertEquals($data, $entity->getValue());
    }

    public function testSetValue(): void
    {
        $data = ['a' => 123, 'b' => null, 'c' => 'test'];

        $entity = new PublicEntity($data);
        self::assertEquals($data, $entity->getValue());

        $entity = new PublicEntity();
        $entity->setValue(['a' => 123]);
        self::assertSame(['a' => 123], $entity->getValue());

        self::assertSame(['a'], $entity->getKeys());
        self::assertTrue(isset($entity->a));

        unset($entity->a);
        self::assertSame([], $entity->getValue());

        $entity['a'] = 90;
        self::assertSame(['a' => 90], $entity->getValue());
        self::assertSame(90, $entity['a']);
        self::assertArrayHasKey('a', $entity);

        unset($entity['a']);
        self::assertSame([], $entity->getValue());

        $entity['a'] = 90;
        foreach ($entity as $key => $value) {
            self::assertSame('a', $key);
            self::assertSame(90, $value);
        }

        self::assertSame('a', $key);
        self::assertSame(90, $value);

        self::assertSame(['a' => 90], $entity->toArray());
        self::assertSame(['a' => 90], $entity->jsonSerialize());
    }

    public function testSecured(): void
    {
        $entity = new SecuredEntity();
        $entity->setValue([
            'name' => 'Antony',
            'id'   => '900'
        ]);

        self::assertSame([], $entity->getValue());

        $entity = new PartiallySecuredEntity();
        $entity->setValue([
            'name' => 'Antony',
            'id'   => 900
        ]);

        self::assertSame([
            'id' => 900
        ], $entity->getValue());
    }

    public function testSetters(): void
    {
        $entity = new FilteredEntity();
        $entity->setValue([
            'name' => 'Antony',
            'id'   => '900'
        ]);

        self::assertSame([
            'id' => 900
        ], $entity->getValue());

        $entity->id = [];

        self::assertSame([
            'id' => 0
        ], $entity->getValue());
    }

    public function testNullable(): void
    {
        $entity = new NullableEntity();
        $entity->setValue([
            'name' => 'Antony',
            'id'   => '900'
        ]);

        self::assertSame([
            'name' => 'Antony',
            'id'   => 900
        ], $entity->getValue());

        // no filter
        $entity->name = null;
        self::assertEquals([
            'name' => null,
            'id'   => 900
        ], $entity->getValue());

        $entity->id = null;
        self::assertEquals([
            'name' => null,
            'id'   => null
        ], $entity->getValue());


        $entity = new FilteredEntity();
        $entity->setValue([
            'name' => 'Antony',
            'id'   => null
        ]);

        self::assertSame([
            'id' => 0
        ], $entity->getValue());
    }

    public function testGetters(): void
    {
        $entity = new GetEntity(['id' => []]);
        self::assertSame(0, $entity->id);

        self::assertSame([
            'id' => 0
        ], $entity->getFields());
    }
}
