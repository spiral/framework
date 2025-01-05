<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Model\Interceptor;

use Mockery as m;
use Spiral\Core\Container;
use Spiral\Core\CoreInterface;
use Spiral\Filters\Model\FilterBag;
use Spiral\Filters\Model\FilterDefinitionInterface;
use Spiral\Filters\Model\FilterInterface;
use Spiral\Filters\Model\HasFilterDefinition;
use Spiral\Filters\Model\Interceptor\ValidateFilterInterceptor;
use Spiral\Filters\Model\ShouldBeValidated;
use Spiral\Filters\Exception\ValidationException;
use Spiral\Models\AbstractEntity;
use Spiral\Tests\Filters\BaseTestCase;
use Spiral\Validation\ValidationInterface;
use Spiral\Validation\ValidationProviderInterface;
use Spiral\Validation\ValidatorInterface;

final class ValidateFilterInterceptorTest extends BaseTestCase
{
    private ValidateFilterInterceptor $interceptor;
    private m\MockInterface $validationProvider;

    public function setUp(): void
    {
        parent::setUp();

        $this->interceptor = new ValidateFilterInterceptor(
            $this->container = new Container()
        );

        $this->container->bind(
            ValidationProviderInterface::class,
            $this->validationProvider = m::mock(ValidationProviderInterface::class)
        );
    }

    public function testFilterWithoutFilterDefinitionShouldNotBeValidated(): void
    {
        $core = m::mock(CoreInterface::class);
        $core->shouldReceive('callAction')->once()->andReturn($filter = m::mock(FilterInterface::class));

        self::assertSame($filter, $this->interceptor->process('foo', 'bar', [
            'filterBag' => new FilterBag($filter, m::mock(AbstractEntity::class), [], [])
        ], $core));
    }

    public function testFilterWithoutShouldBeValidatedInterfaceShouldNotBeValidated(): void
    {
        $core = m::mock(CoreInterface::class);

        $core->shouldReceive('callAction')->once()
            ->andReturn($filter = m::mock(FilterInterface::class, HasFilterDefinition::class));

        $filter->shouldReceive('filterDefinition')->once()->andReturn(m::mock(FilterDefinitionInterface::class));

        self::assertSame($filter, $this->interceptor->process('foo', 'bar', [
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

        $this->validationProvider->shouldReceive('getValidation')->once()->with($definition::class)
            ->andReturn($validation = m::mock(ValidationInterface::class));

        $validation->shouldReceive('validate')->with($bag, $rules, 'context-data')->andReturn(
            $validator = m::mock(ValidatorInterface::class)
        );

        $validator->shouldReceive('isValid')->once()->andReturnTrue();

        self::assertSame($filter, $this->interceptor->process('foo', 'bar', [
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

        $this->validationProvider->shouldReceive('getValidation')->once()->with($definition::class)
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
