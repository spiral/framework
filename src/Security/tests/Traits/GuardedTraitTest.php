<?php

declare(strict_types=1);

namespace Spiral\Tests\Security\Traits;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Spiral\Core\Container;
use Spiral\Core\ContainerScope;
use Spiral\Core\Exception\ScopeException;
use Spiral\Security\GuardInterface;
use Spiral\Tests\Security\Traits\Fixtures\Guarded;
use Spiral\Tests\Security\Traits\Fixtures\GuardedWithNamespace;
use Spiral\Security\Traits\GuardedTrait;

class GuardedTraitTest extends TestCase
{
    public const OPERATION = 'test';
    public const CONTEXT   = [];

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|GuardedTrait
     */
    private $trait;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|GuardInterface
     */
    private $guard;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ContainerInterface
     */
    private $container;

    public function setUp(): void
    {
        $this->trait = $this->getMockForTrait(GuardedTrait::class);
        $this->guard = $this->createMock(GuardInterface::class);
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function testGetGuardFromContainer(): void
    {
        $this->container->method('has')->willReturn(true);
        $this->container->method('get')->will($this->returnValue($this->guard));

        ContainerScope::runScope($this->container, function (): void {
            $this->assertEquals($this->guard, $this->trait->getGuard());
        });
    }

    public function testGuardScopeException(): void
    {
        $this->expectException(ScopeException::class);

        $this->container->method('has')->willReturn(false);

        ContainerScope::runScope($this->container, function (): void {
            $this->assertEquals($this->guard, $this->trait->getGuard());
        });
    }

    public function testGuardScopeException2(): void
    {
        $this->expectException(ScopeException::class);

        $this->assertEquals($this->guard, $this->trait->getGuard());
    }

    public function testAllows(): void
    {
        $this->guard->method('allows')
            ->with(static::OPERATION, static::CONTEXT)
            ->will($this->returnValue(true))
        ;

        $guarded = new Guarded();

        $container = new Container();
        $container->bind(GuardInterface::class, $this->guard);

        ContainerScope::runScope($container, function () use ($guarded): void {
            $this->assertTrue($guarded->allows(static::OPERATION, static::CONTEXT));
            $this->assertFalse($guarded->denies(static::OPERATION, static::CONTEXT));
        });
    }

    public function testResolvePermission(): void
    {
        $guarded = new Guarded();
        $this->assertEquals(static::OPERATION, $guarded->resolvePermission(static::OPERATION));

        $guarded = new GuardedWithNamespace();
        $resolvedPermission = GuardedWithNamespace::GUARD_NAMESPACE . '.' . static::OPERATION;
        $this->assertEquals($resolvedPermission, $guarded->resolvePermission(static::OPERATION));
    }
}
