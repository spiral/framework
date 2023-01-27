<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Scope;

use Psr\Container\ContainerInterface;
use Spiral\Core\Container;
use Spiral\Tests\Core\Fixtures\SampleClass;
use stdClass;

final class UseCaseTest extends BaseTest
{
    /**
     * Parent container won't be destroyed when child container is destroyed.
     * @see Container::destruct()
     */
    public function testChildContainerDestruction(): void
    {
        $root = new Container();
        $root->bind('foo', SampleClass::class);

        $root->scope(function (ContainerInterface $c1) {
            $c1->get('foo');
        }, bindings: ['foo' => SampleClass::class]);

        self::assertInstanceOf(SampleClass::class, $root->get('foo'));
    }

    /**
     * A child scope bindings are not singleton.
     *
     * @dataProvider provideScopeBindingsAsNotSingletons
     */
    public function testScopeBindingsAsNotSingletons(bool $theSame, string $alias, mixed $definition): void
    {
        $root = new Container();

        $root->scope(function (ContainerInterface $c1) use ($theSame, $alias) {
            $obj1 = $c1->get($alias);
            $obj2 = $c1->get($alias);

            $theSame
                ? self::assertSame($obj1, $obj2)
                : self::assertNotSame($obj1, $obj2);
        }, bindings: [$alias => $definition]);
    }

    public function provideScopeBindingsAsNotSingletons(): iterable {
        yield 'array-factory' => [false, 'foo', [self::class, 'makeStdClass']];
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

        $root->scope(function (ContainerInterface $c1) use ($root) {
            $obj1 = $c1->get('foo');
            $this->weakMap->offsetSet($obj1, true);

            self::assertNotSame($root, $c1);
            self::assertInstanceOf(stdClass::class, $obj1);

            $c1->scope(function (ContainerInterface $c2) use ($root, $c1, $obj1) {
                $obj2 = $c2->get('foo');
                $this->weakMap->offsetSet($obj2, true);

                self::assertNotSame($root, $c2);
                self::assertNotSame($c1, $c2);
                self::assertInstanceOf(stdClass::class, $obj2);
                self::assertNotSame($obj1, $obj2);
            }, bindings: ['foo' => [self::class, 'makeStdClass']]);

            // $obj2 should be garbage collected
            self::assertCount(1, $this->weakMap);
        }, bindings: ['foo' => [self::class, 'makeStdClass']]);

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
        $root->bindSingleton('bar', [self::class, 'makeStdClass']);
        $root->bind(stdClass::class, new stdClass());

        $root->scope(function (ContainerInterface $c1) use ($root) {
            $obj1 = $c1->get('foo');
            $this->weakMap->offsetSet($obj1, true);

            self::assertInstanceOf(stdClass::class, $obj1);
            // Singleton must be the same
            self::assertSame($c1->get('bar'), $root->get('bar'));
            $c1->scope(function (ContainerInterface $c2) use ($root, $obj1) {
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
        }, bindings: ['foo' => [self::class, 'makeStdClass']]);
    }
}
