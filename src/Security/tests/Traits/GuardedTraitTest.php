<?php

declare(strict_types=1);

namespace Spiral\Tests\Security\Traits;

use PHPUnit\Framework\MockObject\MockObject;
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

    private object $trait;

    private MockObject&GuardInterface $guard;

    private MockObject&ContainerInterface $container;

    public function setUp(): void
    {
        $this->trait = new class {
            use GuardedTrait;
        };
        $this->guard = $this->createMock(GuardInterface::class);
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function testGetGuardFromContainer(): void
    {
        $this->container->method('has')->willReturn(true);
        $this->container->method('get')->willReturn($this->guard);

        ContainerScope::runScope($this->container, function (): void {
            self::assertEquals($this->guard, $this->trait->getGuard());
        });
    }

    public function testGuardScopeException(): void
    {
        $this->expectException(ScopeException::class);

        $this->container->method('has')->willReturn(false);

        ContainerScope::runScope($this->container, function (): void {
            self::assertEquals($this->guard, $this->trait->getGuard());
        });
    }

    public function testGuardScopeException2(): void
    {
        $this->expectException(ScopeException::class);

        self::assertEquals($this->guard, $this->trait->getGuard());
    }

    public function testAllows(): void
    {
        $this->guard->method('allows')
            ->with(static::OPERATION, static::CONTEXT)
            ->willReturn(true)
        ;

        $guarded = new Guarded();

        $container = new Container();
        $container->bind(GuardInterface::class, $this->guard);

        ContainerScope::runScope($container, function () use ($guarded): void {
            self::assertTrue($guarded->allows(static::OPERATION, static::CONTEXT));
            self::assertFalse($guarded->denies(static::OPERATION, static::CONTEXT));
        });
    }

    public function testResolvePermission(): void
    {
        $guarded = new Guarded();
        self::assertSame(static::OPERATION, $guarded->resolvePermission(static::OPERATION));

        $guarded = new GuardedWithNamespace();
        $resolvedPermission = GuardedWithNamespace::GUARD_NAMESPACE . '.' . static::OPERATION;
        self::assertSame($resolvedPermission, $guarded->resolvePermission(static::OPERATION));
    }
}
