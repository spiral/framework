<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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
        $this->assertEquals(123, $entity->getField('abc'));

        $this->assertTrue($entity->hasField('abc'));
        $this->assertFalse($entity->hasField('bce'));
    }

    public function testMagicProperties(): void
    {
        $entity = new DataEntity();
        $entity->abc = 123;
        $this->assertEquals(123, $entity->abc);

        $this->assertTrue(isset($entity->abc));
    }

    public function testPackingSimple(): void
    {
        $entity = new DataEntity(['a' => 'b', 'c' => 10]);
        $this->assertSame(['a' => 'b', 'c' => 10], $entity->getValue());
    }

    public function testSerialize(): void
    {
        $data = ['a' => 123, 'b' => null, 'c' => 'test'];

        $entity = new DataEntity($data);
        $this->assertEquals($data, $entity->getValue());
    }

    public function testSetValue(): void
    {
        $data = ['a' => 123, 'b' => null, 'c' => 'test'];

        $entity = new PublicEntity($data);
        $this->assertEquals($data, $entity->getValue());

        $entity = new PublicEntity();
        $entity->setValue(['a' => 123]);
        $this->assertEquals(['a' => 123], $entity->getValue());

        $this->assertSame(['a'], $entity->getKeys());
        $this->assertTrue(isset($entity->a));

        unset($entity->a);
        $this->assertEquals([], $entity->getValue());

        $entity['a'] = 90;
        $this->assertEquals(['a' => 90], $entity->getValue());
        $this->assertSame(90, $entity['a']);
        $this->assertTrue(isset($entity['a']));

        unset($entity['a']);
        $this->assertEquals([], $entity->getValue());

        $entity['a'] = 90;
        foreach ($entity as $key => $value) {
            $this->assertSame('a', $key);
            $this->assertSame(90, $value);
        }

        $this->assertSame('a', $key);
        $this->assertSame(90, $value);

        $this->assertEquals(['a' => 90], $entity->toArray());
        $this->assertEquals(['a' => 90], $entity->jsonSerialize());
    }

    public function testSecured(): void
    {
        $entity = new SecuredEntity();
        $entity->setValue([
            'name' => 'Antony',
            'id'   => '900'
        ]);

        $this->assertEquals([], $entity->getValue());

        $entity = new PartiallySecuredEntity();
        $entity->setValue([
            'name' => 'Antony',
            'id'   => 900
        ]);

        $this->assertEquals([
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

        $this->assertEquals([
            'id' => 900
        ], $entity->getValue());

        $entity->id = [];

        $this->assertEquals([
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

        $this->assertEquals([
            'name' => 'Antony',
            'id'   => 900
        ], $entity->getValue());

        // no filter
        $entity->name = null;
        $this->assertEquals([
            'name' => null,
            'id'   => 900
        ], $entity->getValue());

        $entity->id = null;
        $this->assertEquals([
            'name' => null,
            'id'   => null
        ], $entity->getValue());


        $entity = new FilteredEntity();
        $entity->setValue([
            'name' => 'Antony',
            'id'   => null
        ]);

        $this->assertEquals([
            'id' => 0
        ], $entity->getValue());
    }

    public function testGetters(): void
    {
        $entity = new GetEntity(['id' => []]);
        $this->assertSame(0, $entity->id);

        $this->assertEquals([
            'id' => 0
        ], $entity->getFields());
    }
}
