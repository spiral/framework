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
use Spiral\Models\ModelSchema;
use Spiral\Models\SchematicEntity;

class SchematicEntityTest extends TestCase
{
    public function testFillable(): void
    {
        $schema = [ModelSchema::SECURED => []];

        $data = ['a' => 1, 'b' => 2, 'c' => 3];

        $entity = new SchematicEntity([], $schema);
        $entity->setFields($data);
        $this->assertSame($data, $entity->getFields());
    }

    public function testFillable2(): void
    {
        $schema = [ModelSchema::FILLABLE => '*'];

        $data = ['a' => 1, 'b' => 2, 'c' => 3];

        $entity = new SchematicEntity([], $schema);
        $entity->setFields($data);
        $this->assertSame($data, $entity->getFields());
    }

    public function testSecured(): void
    {
        $schema = [ModelSchema::SECURED => '*'];

        $data = ['a' => 1, 'b' => 2, 'c' => 3];

        $entity = new SchematicEntity([], $schema);
        $entity->setFields($data);
        $this->assertSame([], $entity->getFields());
    }

    public function testPartiallySecured(): void
    {
        $schema = [
            ModelSchema::SECURED  => '*',
            ModelSchema::FILLABLE => ['a', 'b'],
        ];

        $data = ['a' => 1, 'b' => 2, 'c' => 3];

        $entity = new SchematicEntity([], $schema);
        $entity->setFields($data);
        $this->assertSame(['a' => 1, 'b' => 2], $entity->getFields());
    }

    public function getSetters(): void
    {
        $schema = [
            ModelSchema::MUTATORS => [
                'setter' => ['a' => 'intval'],
            ],
        ];

        $entity = new SchematicEntity([], $schema);
        $entity->setField('a', '123');

        $this->assertInternalType('int', $entity->getField('a'));
        $this->assertSame(123, $entity->getField('a'));

        $entity->a = '800';
        $this->assertInternalType('int', $entity->a);
        $this->assertSame(800, $entity->a);
    }

    public function testGetters(): void
    {
        $schema = [
            ModelSchema::MUTATORS => [
                'getter' => ['a' => 'intval'],
            ],
        ];

        $entity = new SchematicEntity([], $schema);

        $entity->setField('a', false);
        $this->assertInternalType('int', $entity->getField('a'));
        $this->assertInternalType('bool', $entity->getValue()['a']);

        $entity->a = 8000.1;
        $this->assertInternalType('int', $entity->a);
        $this->assertInternalType('float', $entity->getValue()['a']);
    }
}
