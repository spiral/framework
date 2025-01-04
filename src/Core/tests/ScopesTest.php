<?php

declare(strict_types=1);

namespace Spiral\Tests\Core;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Spiral\Core\Attribute\Singleton;
use Spiral\Core\Container;
use Spiral\Core\ContainerScope;
use Spiral\Core\Exception\RuntimeException;
use Spiral\Tests\Core\Fixtures\Bucket;
use Spiral\Tests\Core\Fixtures\SampleClass;

class ScopesTest extends TestCase
{
    public function testScope(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        self::assertNull(ContainerScope::getContainer());

        self::assertTrue(ContainerScope::runScope($container, static fn(): bool => $container === ContainerScope::getContainer()));

        self::assertNull(ContainerScope::getContainer());
    }

    public function testScopeException(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        self::assertNull(ContainerScope::getContainer());

        try {
            self::assertTrue(ContainerScope::runScope($container, static function (): never {
                throw new RuntimeException('exception');
            }));
        } catch (\Throwable $e) {
        }

        self::assertInstanceOf(RuntimeException::class, $e);
        self::assertNull(ContainerScope::getContainer());
    }

    public function testContainerScope(): void
    {
        $c = new Container();
        $c->bind('bucket', new Bucket('a'));

        self::assertSame('a', $c->get('bucket')->getName());
        self::assertFalse($c->has('other'));

        self::assertTrue($c->runScope([
            'bucket' => new Bucket('b'),
            'other'  => new SampleClass()
        ], function ($c): bool {
            self::assertSame('b', $c->get('bucket')->getName());
            self::assertTrue($c->has('other'));

            return $c->get('bucket')->getName() == 'b' && $c->has('other');
        }));

        self::assertSame('a', $c->get('bucket')->getName());
        self::assertFalse($c->has('other'));
    }

    public function testContainerScopeException(): void
    {
        $c = new Container();
        $c->bind('bucket', new Bucket('a'));

        self::assertSame('a', $c->get('bucket')->getName());
        self::assertFalse($c->has('other'));

        self::assertTrue($c->runScope([
            'bucket' => new Bucket('b'),
            'other'  => new SampleClass()
        ], function ($c): bool {
            self::assertSame('b', $c->get('bucket')->getName());
            self::assertTrue($c->has('other'));

            return $c->get('bucket')->getName() == 'b' && $c->has('other');
        }));

        try {
            self::assertTrue($c->runScope([
                'bucket' => new Bucket('b'),
                'other'  => new SampleClass()
            ], function () use ($c): void {
                throw new RuntimeException('exception');
            }));
        } catch (\Throwable) {
        }

        self::assertSame('a', $c->get('bucket')->getName());
        self::assertFalse($c->has('other'));
    }

    public function testContainerInScope(): void
    {
        $container = new Container();

        self::assertSame($container, ContainerScope::runScope($container, static fn (ContainerInterface $container): \Psr\Container\ContainerInterface => $container));

        $result = ContainerScope::runScope(
            $container,
            static fn(Container $container): mixed => $container->runScope(
                [],
                static fn (Container $container): \Spiral\Core\Container => $container,
            ),
        );

        self::assertSame($container, $result);
    }

    public function testSingletonRebindingInScope(): void
    {
        $c = new Container();
        $c->bindSingleton('bucket', new Container\Autowire(Bucket::class, ['a']));

        self::assertSame('a', $c->get('bucket')->getName());

        self::assertTrue($c->runScope([
            'bucket' => new Bucket('b'),
        ], function ($c): bool {
            self::assertSame('b', $c->get('bucket')->getName());

            return $c->get('bucket')->getName() === 'b';
        }));

        self::assertSame('a', $c->get('bucket')->getName());
    }

    public function testHasInstanceAfterMakeWithoutAliasInScope(): void
    {
        $container = new Container();
        $container->bindSingleton('test', new #[Singleton] class {});
        $container->make('test');

        $container->runScoped(function (Container $container): void {
            self::assertTrue($container->hasInstance('test'));
        });
    }

    public function testHasInstanceAfterMakeWithAliasInScope(): void
    {
        $container = new Container();
        $container->bindSingleton('test', SampleClass::class);
        $container->make('test');

        $container->runScoped(function (Container $container): void {
            self::assertTrue($container->hasInstance('test'));
        });
    }

    public function testHasInstanceAfterMakeWithNestedAliasInScope(): void
    {
        $container = new Container();

        $container->bindSingleton('sampleClass', SampleClass::class);
        $container->bindSingleton('foo', 'sampleClass');

        $container->bindSingleton('bar', 'foo');
        $container->make('bar');

        $container->runScoped(function (Container $container): void {
            self::assertTrue($container->hasInstance('bar'));
        });
    }
}
