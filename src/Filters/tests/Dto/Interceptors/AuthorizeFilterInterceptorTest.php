<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Dto\Interceptors;

use Mockery as m;
use Psr\Container\ContainerInterface;
use Spiral\Auth\AuthContextInterface;
use Spiral\Core\CoreInterface;
use Spiral\Filters\Dto\FilterInterface;
use Spiral\Filters\Dto\Interceptors\AuthorizeFilterInterceptor;
use Spiral\Filters\Dto\ShouldBeAuthorized;
use Spiral\Filters\Exception\AuthorizationException;
use Spiral\Tests\Filters\BaseTest;

final class AuthorizeFilterInterceptorTest extends BaseTest
{
    private AuthorizeFilterInterceptor $interceptor;

    public function setUp(): void
    {
        parent::setUp();

        $this->interceptor = new AuthorizeFilterInterceptor(
            $this->container = m::mock(ContainerInterface::class)
        );
    }

    public function testFilterWithoutInterfaceShouldNotBeAuthorized(): void
    {
        $core = m::mock(CoreInterface::class);

        $core->shouldReceive('callAction')->once()->andReturn($filter = m::mock(FilterInterface::class));

        $this->assertSame($filter, $this->interceptor->process('foo', 'bar', [], $core));
    }

    public function testFilterWithInterfaceShouldBeAuthorized(): void
    {
        $core = m::mock(CoreInterface::class);

        $core->shouldReceive('callAction')->once()
            ->andReturn($filter = m::mock(FilterInterface::class, ShouldBeAuthorized::class));

        $this->container->shouldReceive('has')->with(AuthContextInterface::class)->andReturnTrue();
        $this->container->shouldReceive('get')->with(AuthContextInterface::class)
            ->andReturn($context = m::mock(AuthContextInterface::class));

        $filter->shouldReceive('isAuthorized')->with($context)->andReturnTrue();

        $this->assertSame($filter, $this->interceptor->process('foo', 'bar', [], $core));
    }

    public function testFilterWithInterfaceCanBeAuthorizedWithNullAuthContext(): void
    {
        $core = m::mock(CoreInterface::class);

        $core->shouldReceive('callAction')->once()
            ->andReturn($filter = m::mock(FilterInterface::class, ShouldBeAuthorized::class));

        $this->container->shouldReceive('has')->with(AuthContextInterface::class)->andReturnFalse();

        $filter->shouldReceive('isAuthorized')->with(null)->andReturnTrue();

        $this->assertSame($filter, $this->interceptor->process('foo', 'bar', [], $core));
    }

    public function testFilterWithInterfaceWithoutAuthorizationShouldThrowAnException(): void
    {
        $this->expectException(AuthorizationException::class);
        $core = m::mock(CoreInterface::class);

        $core->shouldReceive('callAction')->once()
            ->andReturn($filter = m::mock(FilterInterface::class, ShouldBeAuthorized::class));

        $this->container->shouldReceive('has')->with(AuthContextInterface::class)->andReturnTrue();
        $this->container->shouldReceive('get')->with(AuthContextInterface::class)
            ->andReturn($context = m::mock(AuthContextInterface::class));

        $filter->shouldReceive('isAuthorized')->with($context)->andReturnFalse();
        $this->interceptor->process('foo', 'bar', [], $core);
    }
}
