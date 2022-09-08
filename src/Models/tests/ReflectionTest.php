<?php

declare(strict_types=1);

namespace Spiral\Tests\Models;

use PHPUnit\Framework\TestCase;
use Spiral\Models\DataEntity;
use Spiral\Models\Reflection\ReflectionEntity;
use Spiral\Models\SchematicEntity;

class ReflectionTest extends TestCase
{
    public function testReflection(): void
    {
        $schema = new ReflectionEntity(TestModel::class);
        $this->assertEquals(new \ReflectionClass(TestModel::class), $schema->getReflection());
    }

    public function testFillable(): void
    {
        $schema = new ReflectionEntity(TestModel::class);
        $this->assertSame(['value'], $schema->getFillable());
    }

    public function testFillableExtended(): void
    {
        $schema = new ReflectionEntity(ExtendedModel::class);
        $this->assertSame(['value', 'name'], $schema->getFillable());
    }

    public function testSetters(): void
    {
        $schema = new ReflectionEntity(TestModel::class);
        $this->assertSame(
            [
                'value' => 'intval'
            ],
            $schema->getSetters()
        );
    }

    public function testSettersExtended(): void
    {
        $schema = new ReflectionEntity(ExtendedModel::class);
        $this->assertSame(
            [
                'value' => 'intval',
                'name'  => 'strval'
            ],
            $schema->getSetters()
        );
    }

    public function testSecured(): void
    {
        $schema = new ReflectionEntity(ExtendedModel::class);
        $this->assertSame(['name'], $schema->getSecured());
    }

    public function testDeclaredMethods(): void
    {
        $schema = new ReflectionEntity(ExtendedModel::class);
        $this->assertEquals(
            [
                new \ReflectionMethod(ExtendedModel::class, 'methodB')
            ],
            $schema->declaredMethods()
        );
    }

    public function testGetSecured(): void
    {
        $schema = new ReflectionEntity(TestModel::class);
        $this->assertSame('*', $schema->getSecured());
    }

    public function testGetReflectionValues(): void
    {
        $schema = new ReflectionEntity(ExtendedModel::class);

        $this->assertSame([
            'value' => 'intval',
            'name'  => 'strtoupper'
        ], $schema->getGetters());

        $this->assertSame([
            'value' => 'intval',
            'name'  => 'strval'
        ], $schema->getSetters());
    }

    public function testGetSchema(): void
    {
        $schema = new ReflectionEntity(SchemaModel::class);
        $this->assertSame(['nice'], $schema->getSchema());

        $schema = new ReflectionEntity(SchemaModelB::class);
        $this->assertSame(['nice', 'nice2'], $schema->getSchema());
    }

    public function testGetSchemaNotSchematic(): void
    {
        $schema = new ReflectionEntity(SchemaModelC::class);
        $this->assertSame(['nice2'], $schema->getSchema());
    }
}
