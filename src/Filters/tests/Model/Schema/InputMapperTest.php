<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Model\Schema;

use Mockery as m;
use Spiral\Filters\Model\FilterInterface;
use Spiral\Filters\Model\FilterProviderInterface;
use Spiral\Filters\Model\Schema\Builder;
use Spiral\Filters\Model\Schema\InputMapper;
use Spiral\Filters\Exception\ValidationException;
use Spiral\Filters\InputInterface;
use Spiral\Tests\Filters\BaseTestCase;
use Spiral\Tests\Filters\Fixtures\NestedFilter;

final class InputMapperTest extends BaseTestCase
{
    private m\LegacyMockInterface|m\MockInterface|FilterProviderInterface $provider;
    private InputMapper $mapper;

    public function testMapSchema(): void
    {
        $input = m::mock(InputInterface::class);

        $input->shouldReceive('getValue')->once()->with('data', 'id')->andReturn('id-value');
        $input->shouldReceive('getValue')->once()->with('input', 'username')->andReturn('username-value');

        // nested
        $input->shouldReceive('withPrefix')->once()
            ->with('nested')
            ->andReturn($nestedInput = m::mock(InputInterface::class));

        $this->provider->shouldReceive('createFilter')->once()
            ->with(NestedFilter::class, $nestedInput)
            ->andReturn($nestedFilter = m::mock(FilterInterface::class));

        // nested_error
        $input->shouldReceive('withPrefix')->once()
            ->with('nested_error')
            ->andReturn($nestedInput = m::mock(InputInterface::class));

        $this->provider->shouldReceive('createFilter')->once()
            ->with(NestedFilter::class, $nestedInput)
            ->andThrow(new ValidationException(['nested_error_field' => 'Error']));

        // nested1
        $input->shouldReceive('withPrefix')->once()
            ->with('foo')
            ->andReturn($nestedInput1 = m::mock(InputInterface::class));

        $this->provider->shouldReceive('createFilter')->once()
            ->with(NestedFilter::class, $nestedInput1)
            ->andReturn($nestedFilter1 = m::mock(FilterInterface::class));

        // nested2
        $input->shouldReceive('getValue')->once()->with('data', 'nested2')->andReturn(
            ['first' => 'foo', 'second' => 'foo'],
        );

        $nestedInput2 = m::mock(InputInterface::class);
        $input->shouldReceive('withPrefix')->once()->with('nested2.first')->andReturn($nestedInput2);
        $input->shouldReceive('withPrefix')->once()->with('nested2.second')->andReturn($nestedInput2);

        $this->provider->shouldReceive('createFilter')->twice()
            ->with(NestedFilter::class, $nestedInput2)
            ->andReturn($nestedFilter2 = m::mock(FilterInterface::class));

        // nested3
        $input->shouldReceive('getValue')->once()->with('data', 'foo')->andReturn(
            ['first' => 'foo', 'second' => 'foo'],
        );

        $nestedInput3 = m::mock(InputInterface::class);
        $input->shouldReceive('withPrefix')->once()->with('foo.first')->andReturn($nestedInput3);
        $input->shouldReceive('withPrefix')->once()->with('foo.second')->andReturn($nestedInput3);

        $this->provider->shouldReceive('createFilter')->once()
            ->with(NestedFilter::class, $nestedInput3)
            ->andReturn($nestedFilter3 = m::mock(FilterInterface::class));

        $this->provider->shouldReceive('createFilter')->once()
            ->with(NestedFilter::class, $nestedInput3)
            ->andThrow(new ValidationException(['foo.second' => 'Error']));

        [$data, $errors] = $this->mapper->map(
            (new Builder())->makeSchema('foo', [
                'id' => 'data:id',
                'username' => 'input:username',
                'nested' => NestedFilter::class,
                'nested_error' => NestedFilter::class,
                'nested1' => [NestedFilter::class, 'foo'],
                'nested2' => [NestedFilter::class],
                'nested3' => [NestedFilter::class, 'foo.*'],
            ]),
            $input,
        );

        self::assertSame([
            'id' => 'id-value',
            'username' => 'username-value',
            'nested' => $nestedFilter,
            'nested1' => $nestedFilter1,
            'nested2' => [
                'first' => $nestedFilter2,
                'second' => $nestedFilter2,
            ],
            'nested3' => [
                'first' => $nestedFilter3,
            ],
        ], $data);
        self::assertSame([
            'nested_error' => [
                'nested_error_field' => 'Error',
            ],
            'nested3' => [
                'second' => [
                    'foo.second' => 'Error',
                ],
            ],
        ], $errors);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = new InputMapper(
            $this->provider = m::mock(FilterProviderInterface::class),
        );
    }
}
