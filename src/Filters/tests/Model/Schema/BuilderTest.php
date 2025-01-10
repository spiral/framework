<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Model\Schema;

use Spiral\Filters\Model\Schema\Builder;
use Spiral\Filters\Exception\SchemaException;
use Spiral\Tests\Filters\BaseTestCase;
use Spiral\Tests\Filters\Fixtures\NestedFilter;

final class BuilderTest extends BaseTestCase
{
    private Builder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new Builder();
    }

    public function testBuildSchema(): void
    {
        $schema = $this->builder->makeSchema('foo', [
            'id' => 'data:id',
            'username' => 'input:username',
            'nested' => NestedFilter::class,
            'nested1' => [NestedFilter::class, 'foo'],
            'nested2' => [NestedFilter::class],
            'nested3' => [NestedFilter::class, 'foo.*'],
            'nested4' => [NestedFilter::class, 'query:foo.*']
        ]);

        self::assertSame([
            'id' => [
                Builder::SCHEMA_SOURCE => 'data',
                Builder::SCHEMA_ORIGIN => 'id',
            ],
            'username' => [
                Builder::SCHEMA_SOURCE => 'input',
                Builder::SCHEMA_ORIGIN => 'username',
            ],
            'nested' => [
                Builder::SCHEMA_SOURCE => null,
                Builder::SCHEMA_ORIGIN => 'nested',
                Builder::SCHEMA_FILTER => NestedFilter::class,
                Builder::SCHEMA_ARRAY => false,
                Builder::SCHEMA_OPTIONAL => false,
            ],
            'nested1' => [
                Builder::SCHEMA_FILTER => NestedFilter::class,
                Builder::SCHEMA_SOURCE => null,
                Builder::SCHEMA_ORIGIN => 'foo',
                Builder::SCHEMA_ARRAY => false,
                Builder::SCHEMA_OPTIONAL => false,
            ],
            'nested2' => [
                Builder::SCHEMA_FILTER => NestedFilter::class,
                Builder::SCHEMA_SOURCE => null,
                Builder::SCHEMA_ORIGIN => 'nested2',
                Builder::SCHEMA_ARRAY => true,
                Builder::SCHEMA_OPTIONAL => false,
                Builder::SCHEMA_ITERATE_SOURCE => 'data',
                Builder::SCHEMA_ITERATE_ORIGIN => 'nested2',
            ],
            'nested3' => [
                Builder::SCHEMA_FILTER => NestedFilter::class,
                Builder::SCHEMA_SOURCE => null,
                Builder::SCHEMA_ORIGIN => 'foo',
                Builder::SCHEMA_ARRAY => true,
                Builder::SCHEMA_OPTIONAL => false,
                Builder::SCHEMA_ITERATE_SOURCE => 'data',
                Builder::SCHEMA_ITERATE_ORIGIN => 'foo',
            ],
            'nested4' => [
                Builder::SCHEMA_FILTER => NestedFilter::class,
                Builder::SCHEMA_SOURCE => null,
                Builder::SCHEMA_ORIGIN => 'query:foo',
                Builder::SCHEMA_ARRAY => true,
                Builder::SCHEMA_OPTIONAL => false,
                Builder::SCHEMA_ITERATE_SOURCE => 'query',
                Builder::SCHEMA_ITERATE_ORIGIN => 'foo',
            ]
        ], $schema);
    }

    public function testUndefinedSchema(): void
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionMessage('Filter `foo` does not define any schema');

        $this->builder->makeSchema('foo', []);
    }

    public function testEmptySchema(): void
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionMessage('Invalid schema definition at `foo`->`id`');

        $this->builder->makeSchema('foo', ['id' => []]);
    }
}
