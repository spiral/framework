<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Interceptors;

use Mockery as m;
use Psr\Container\ContainerInterface;
use Spiral\Core\CoreInterface;
use Spiral\Filters\Exception\ValidationException;
use Spiral\Filters\FilterBag;
use Spiral\Filters\FilterDefinitionInterface;
use Spiral\Filters\FilterInterface;
use Spiral\Filters\HasFilterDefinition;
use Spiral\Filters\Interceptors\ValidateFilterInterceptor;
use Spiral\Filters\ShouldBeValidated;
use Spiral\Models\AbstractEntity;
use Spiral\Tests\Filters\BaseTest;
use Spiral\Validation\ValidationInterface;
use Spiral\Validation\ValidationProviderInterface;
use Spiral\Validation\ValidatorInterface;

final class ValidateFilterInterceptorTest extends BaseTest
{
    private ValidateFilterInterceptor $interceptor;

    public function setUp(): void
    {
        parent::setUp();

        $this->interceptor = new ValidateFilterInterceptor(
            $this->container = m::mock(ContainerInterface::class)
        );
    }

    public function testFilterWithoutFilterDefinitionShouldNotBeValidated(): void
    {
        $core = m::mock(CoreInterface::class);
        $core->shouldReceive('callAction')->once()->andReturn($filter = m::mock(FilterInterface::class));

        $this->assertSame($filter, $this->interceptor->process('foo', 'bar', [
            'filterBag' => new FilterBag($filter, m::mock(AbstractEntity::class), [], [])
        ], $core));
    }

    public function testFilterWithoutShouldBeValidatedInterfaceShouldNotBeValidated(): void
    {
        $core = m::mock(CoreInterface::class);

        $core->shouldReceive('callAction')->once()
            ->andReturn($filter = m::mock(FilterInterface::class, HasFilterDefinition::class));

        $filter->shouldReceive('filterDefinition')->once()->andReturn(m::mock(FilterDefinitionInterface::class));

        $this->assertSame($filter, $this->interceptor->process('foo', 'bar', [
            'filterBag' => new FilterBag($filter, m::mock(AbstractEntity::class), [], [])
        ], $core));
    }

    public function testFilterWithShouldBeValidatedInterfaceShouldBeValidated(): void
    {
        $core = m::mock(CoreInterface::class);

        $core->shouldReceive('callAction')->once()
            ->andReturn($filter = m::mock(FilterInterface::class, HasFilterDefinition::class));

        $bag = new FilterBag($filter, m::mock(AbstractEntity::class), [], []);

        $filter->shouldReceive('filterDefinition')->once()
            ->andReturn($definition = m::mock(FilterDefinitionInterface::class, ShouldBeValidated::class));

        $definition->shouldReceive('validationRules')->once()->andReturn($rules = ['baz' => 'bar']);

        $this->container->shouldReceive('get')->with(ValidationProviderInterface::class)
            ->andReturn($provider = m::mock(ValidationProviderInterface::class));

        $provider->shouldReceive('getValidation')->once()->with($definition::class)
            ->andReturn($validation = m::mock(ValidationInterface::class));

        $validation->shouldReceive('validate')->with($bag, $rules, 'context-data')->andReturn(
            $validator = m::mock(ValidatorInterface::class)
        );

        $validator->shouldReceive('isValid')->once()->andReturnTrue();

        $this->assertSame($filter, $this->interceptor->process('foo', 'bar', [
            'filterBag' => $bag,
            'context' => 'context-data'
        ], $core));
    }

    public function testFilterWithShouldBeValidatedInterfaceShouldThrowAnExceptionWhenNotValid(): void
    {
        $this->expectException(ValidationException::class);

        $core = m::mock(CoreInterface::class);

        $core->shouldReceive('callAction')->once()
            ->andReturn($filter = m::mock(FilterInterface::class, HasFilterDefinition::class));

        $bag = new FilterBag($filter, m::mock(AbstractEntity::class), [], ['bar' => 'baf']);

        $filter->shouldReceive('filterDefinition')->once()
            ->andReturn($definition = m::mock(FilterDefinitionInterface::class, ShouldBeValidated::class));

        $definition->shouldReceive('validationRules')->once()->andReturn($rules = ['baz' => 'bar']);

        $this->container->shouldReceive('get')->with(ValidationProviderInterface::class)
            ->andReturn($provider = m::mock(ValidationProviderInterface::class));

        $provider->shouldReceive('getValidation')->once()->with($definition::class)
            ->andReturn($validation = m::mock(ValidationInterface::class));

        $validation->shouldReceive('validate')->with($bag, $rules, 'context-data')->andReturn(
            $validator = m::mock(ValidatorInterface::class)
        );

        $validator->shouldReceive('isValid')->once()->andReturnFalse();
        $validator->shouldReceive('getErrors')->once()->andReturn(['foo' => 'bar']);

        $this->interceptor->process('foo', 'bar', [
            'filterBag' => $bag,
            'context' => 'context-data'
        ], $core);
    }
}
