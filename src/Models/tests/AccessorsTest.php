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
use Spiral\Models\Reflection\ReflectionEntity;

class AccessorsTest extends TestCase
{
    public function testAccessed(): void
    {
        $e = new AccessedEntity();
        $e->name = 'antony';
        $this->assertSame('ANTONY', (string)$e->name);

        $e->setFields(['name' => 'bob']);
        $this->assertSame('BOB', (string)$e->name);

        $this->assertSame([
            'name' => 'BOB'
        ], $e->getValue());

        $this->assertSame([
            'name' => 'BOB'
        ], $e->jsonSerialize());

        $this->assertEquals([
            'name' => new NameValue('bob')
        ], $e->getFields());

        $e->name = new NameValue('mike');

        $this->assertEquals([
            'name' => new NameValue('mike')
        ], $e->getFields());
    }

    public function testGetAccessor(): void
    {
        $e = new AccessedEntity();
        $this->assertSame('', (string)$e->name);
        $this->assertInstanceOf(NameValue::class, $e->name);

        $this->assertEquals([
            'name' => new NameValue(null)
        ], $e->getFields());

        $e->setFields(null);
    }

    public function testReflection(): void
    {
        $s = new ReflectionEntity(AccessedEntity::class);
        $this->assertSame([
            'name' => NameValue::class
        ], $s->getAccessors());
    }

    /**
     * @expectedException \Spiral\Models\Exception\EntityException
     */
    public function testException(): void
    {
        $e = new BadAccessedEntity();
        $e->name = 'xx';
    }
}
