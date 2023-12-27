<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Scope;

use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Container\ContainerInterface;
use Spiral\Core\Config\Shared;
use Spiral\Core\Container;
use Spiral\Tests\Core\Fixtures\Bucket;
use Spiral\Tests\Core\Fixtures\Factory;
use Spiral\Tests\Core\Fixtures\SampleClass;
use Spiral\Tests\Core\Scope\Stub\FileLogger;
use Spiral\Tests\Core\Scope\Stub\KVLogger;
use Spiral\Tests\Core\Scope\Stub\LoggerInjector;
use Spiral\Tests\Core\Scope\Stub\LoggerInterface;
use stdClass;

final class UseCaseTest extends BaseTestCase
{
    /**
     * Parent container won't be destroyed when child container is destroyed.
     * @see Container::destruct()
     */
    public function testChildContainerDestructionDoesntDestroyParent(): void
    {
        $root = new Container();
        $root->bind('foo', SampleClass::class);

        $root->runScoped(function (ContainerInterface $c1) {
            $c1->get('foo');
        }, bindings: ['foo' => SampleClass::class]);

        self::assertInstanceOf(SampleClass::class, $root->get('foo'));
    }

    /**
     * Todo: may be uncommented when politics about container leak will be decided.
     *
     * Child container must be destroyed after scope completion and mustn't be leaked
     *
    public function testChildContainerDestruction(): void
    {
        $root = new Container();
        $root->bind('foo', SampleClass::class);

        self::expectException(ScopeContainerLeakedException::class);
        self::expectExceptionMessage('Scoped container has been leaked. Scope: "root"->null.');

        $root->runScoped(function (ContainerInterface $c1): callable {
            return fn() => $c1->get('foo');
        });
    } */

    /**
     * A child scope bindings are not singleton.
     */
    #[DataProvider('provideScopeBindingsAsNotSingletons')]
    public function testScopeBindingsAsNotSingletons(bool $theSame, string $alias, mixed $definition): void
    {
        $root = new Container();

        $root->runScoped(function (ContainerInterface $c1) use ($theSame, $alias) {
            $obj1 = $c1->get($alias);
            $obj2 = $c1->get($alias);

            $theSame
                ? self::assertSame($obj1, $obj2)
                : self::assertNotSame($obj1, $obj2);
        }, bindings: [$alias => $definition]);
    }

    public static function provideScopeBindingsAsNotSingletons(): iterable
    {
        yield 'array-factory' => [false, 'foo', [Factory::class, 'makeStdClass']];
        yield 'class-name' => [false, SampleClass::class, SampleClass::class];
        yield 'object' => [true, stdClass::class, new stdClass()];
    }

    /**
     * A nested scope can have its own bindings.
     * Inner scope container is not the same object as the parent.
     */
    public function testScopeDefinition(): void
    {
        $root = new Container();

        $root->runScoped(function (ContainerInterface $c1) use ($root) {
            $obj1 = $c1->get('foo');
            $this->weakMap->offsetSet($obj1, true);

            self::assertNotSame($root, $c1);
            self::assertInstanceOf(stdClass::class, $obj1);

            $c1->runScoped(function (ContainerInterface $c2) use ($root, $c1, $obj1) {
                $obj2 = $c2->get('foo');
                $this->weakMap->offsetSet($obj2, true);

                self::assertNotSame($root, $c2);
                self::assertNotSame($c1, $c2);
                self::assertInstanceOf(stdClass::class, $obj2);
                self::assertNotSame($obj1, $obj2);
            }, bindings: ['foo' => [Factory::class, 'makeStdClass']]);

            // $obj2 should be garbage collected
            self::assertCount(1, $this->weakMap);
        }, bindings: ['foo' => [Factory::class, 'makeStdClass']]);

        // $obj1 should be garbage collected
        self::assertEmpty($this->weakMap);
    }

    /**
     * If a nested scope does not have its own bindings, it will resolve dependencies from the parent.
     * When a component that cannot be resolved in the current scope, Container looks to the parent scope to see
     * if the dependency can be resolved there.
     */
    public function testChildContainerResolvesDepsFromParent(): void
    {
        $root = new Container();
        $root->bindSingleton('bar', [Factory::class, 'makeStdClass']);
        $root->bind(stdClass::class, new stdClass());

        $root->runScoped(function (ContainerInterface $c1) use ($root) {
            $obj1 = $c1->get('foo');
            $this->weakMap->offsetSet($obj1, true);

            self::assertInstanceOf(stdClass::class, $obj1);
            // Singleton must be the same
            self::assertSame($c1->get('bar'), $root->get('bar'));
            $c1->runScoped(function (ContainerInterface $c2) use ($root, $obj1) {
                $obj2 = $c2->get('foo');

                self::assertInstanceOf(stdClass::class, $obj2);
                self::assertNotSame($obj1, $obj2);
                // Singleton must be the same
                self::assertSame($c2->get('bar'), $root->get('bar'));
                // Key is class name but parent has the definition.
                self::assertSame(
                    $c2->get(stdClass::class),
                    $root->get(stdClass::class),
                    "Nested container mustn't create new instance using class name as key without definition."
                );
            });
        }, bindings: ['foo' => [Factory::class, 'makeStdClass']]);
    }

    /**
     * Test ability to preconfigure different scopes.
     */
    public function testBindingScopes(): void
    {
        $root = new Container();
        // Configure Scope 1
        $root->getBinder('scope1')->bindSingleton('foo', (object)['scope' => 'scope1']);
        // Configure Scope 2
        self::assertFalse($root->getBinder('scope2')->hasInstance('foo'));
        $root->getBinder('scope2')->bindSingleton('foo', (object)['scope' => 'scope2']);
        $root->getBinder('scope2')->bindSingleton('bar', (object)['from' => 'default']);

        self::assertFalse($root->has('foo'));

        $root->runScoped(static function (ContainerInterface $c1): void {
            self::assertTrue($c1->has('foo'));
            self::assertFalse($c1->has('bar'));
            self::assertSame('scope1', $c1->get('foo')->scope);

            $c1->runScoped(static function (ContainerInterface $c2): void {
                self::assertTrue($c2->has('foo'));
                self::assertTrue($c2->has('bar'));
                self::assertSame('scope2', $c2->get('foo')->scope);
                self::assertSame('custom', $c2->get('bar')->from);
            }, bindings: ['bar' => (object)['from' => 'custom']], name: 'scope2');
        }, name: 'scope1');
    }

    /**
     * Test binding resolving in different scopes with the same name.
     */
    public function testBindingInFewSameScopes(): void
    {
        $factory = new Factory();
        $root = new Container();
        $root->getBinder('scope1')->bindSingleton('foo', $factory->makeStdClass(...));

        $getter = fn () => $root->runScoped(function (Container $c1) use ($factory): mixed {
            self::assertFalse($c1->has('bar'));
            $c1->bindSingleton('bar', $factory->makeStdClass(...));

            return $c1->get('foo');
        }, name: 'scope1');

        $obj1 = $getter();
        $obj2 = $getter();

        self::assertNotSame($obj1, $obj2);
    }

    /**
     * Test the {@see Container::getBinder()} affects default scope bindings only.
     */
    public function testScopeBinderAffectsDefaultBindingsOnly(): void
    {
        $factory = new Factory();
        $root = new Container();

        $root->runScoped(function (Container $c1) use ($factory): void {
            $c1->getBinder('scope1')->bindSingleton('bar', $factory->makeStdClass(...));
            self::assertFalse($c1->has('bar'));
        }, name: 'scope1');

        $root->runScoped(function (Container $c1): void {
            self::assertTrue($c1->has('bar'));
            self::assertInstanceOf(stdClass::class, $c1->get('bar'));
        }, name: 'scope1');
    }

    public function testInjectorsFromParentScope(): void
    {
        $root = new Container();

        $loggerRoot = new KVLogger();
        $logger1 = new FileLogger();

        $root->bindInjector(LoggerInterface::class, LoggerInjector::class);
        $root->bind(LoggerInjector::class, new Shared(new LoggerInjector($loggerRoot)));
        // Configure Scope 1
        $root->bindInjector(LoggerInterface::class, LoggerInjector::class);
        $root->bind(LoggerInjector::class, new Shared(new LoggerInjector($logger1)));

        self::assertSame($logger1, $root->get(LoggerInterface::class));

        $root->runScoped(static function (ContainerInterface $c1) use ($logger1): void {
            self::assertSame($logger1, $c1->get(LoggerInterface::class));

            $c1->runScoped(static function (ContainerInterface $c2) use ($logger1): void {
                self::assertSame($logger1, $c2->get(LoggerInterface::class));
            }, name: 'scope2');
        }, name: 'scope1');
    }

    public function testSingletonRebindingInScope(): void
    {
        $c = new Container();
        $c->bindSingleton('bucket', new Container\Autowire(Bucket::class, ['a']));

        $this->assertSame('a', $c->get('bucket')->getName());

        $this->assertTrue($c->runScoped(function (ContainerInterface $c): bool {
            $this->assertSame('b', $c->get('bucket')->getName());

            return $c->get('bucket')->getName() === 'b';
        }, bindings: [
            'bucket' => new Bucket('b'),
        ]));

        $this->assertSame('a', $c->get('bucket')->getName());
    }

    public function testRegisterContainerOnInvoke(): void
    {
        $root = new Container();

        $root->invoke(static function () use ($root) {
            self::assertNotNull(\Spiral\Core\ContainerScope::getContainer());
            self::assertSame($root, \Spiral\Core\ContainerScope::getContainer());
        });
    }

    public function testRegisterContainerOnGet(): void
    {
        $root = new Container();
        $root->bind('foo', function () use ($root) {
            self::assertNotNull(\Spiral\Core\ContainerScope::getContainer());
            self::assertSame($root, \Spiral\Core\ContainerScope::getContainer());
        });

        $root->get('foo');
    }

    public function testRegisterContainerOnMake(): void
    {
        $root = new Container();
        $root->bind('foo', function () use ($root) {
            self::assertNotNull(\Spiral\Core\ContainerScope::getContainer());
            self::assertSame($root, \Spiral\Core\ContainerScope::getContainer());
        });

        $root->make('foo');
    }
}
