<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Scope;

use PHPUnit\Framework\Attributes\Group;
use Spiral\Core\Attribute\Scope;
use Spiral\Core\Container;
use Spiral\Core\Exception\Scope\BadScopeException;
use Spiral\Core\Exception\Scope\NamedScopeDuplicationException;
use Spiral\Tests\Core\Scope\Stub\AttrScopeFoo;
use Spiral\Tests\Core\Scope\Stub\AttrScopeFooSingleton;

final class ScopeAttributeTest extends BaseTestCase
{
    /**
     * Just try to make a dependency with a wrong scope.
     */
    public function testBadScope(): void
    {
        self::expectException(BadScopeException::class);

        $root = self::makeContainer();
        $root->make(AttrScopeFooSingleton::class);
    }

    public function testBadScopeWithDisabledChecking(): void
    {
        $root = self::makeContainer(checkScope: false);
        self::assertInstanceOf(AttrScopeFooSingleton::class, $root->make(AttrScopeFooSingleton::class));
    }

    /**
     * Test that a dependency are resolved in the scope specified in the {@see Scope} attribute.
     */
    public function testNamedScopeResolveFromRootInNullScope(): void
    {
        $root = self::makeContainer();

        $root->runScoped(static function (Container $c1): void {
            $c1->runScoped(static function (Container $c2) use ($c1): void {
                $obj1 = $c1->get(AttrScopeFooSingleton::class);
                $obj2 = $c2->get(AttrScopeFooSingleton::class);

                self::assertSame($obj1, $obj2);
            });
        }, name: 'foo');
    }

    public function testNamedScopeResolveFromParentScope(): void
    {
        $root = self::makeContainer();
        $root->getBinder('bar')->bindSingleton('binding', static fn(): AttrScopeFoo => new AttrScopeFoo());

        $root->runScoped(static function (Container $fooScope): void {
            $fooScope->runScoped(static function (Container $container): void {
                self::assertInstanceOf(AttrScopeFoo::class, $container->get('binding'));
            }, name: 'bar');
        }, name: 'foo');
    }

    public function testBadScopeExceptionAllParentNamedScopesNotContainsNeededScope(): void
    {
        self::expectException(BadScopeException::class);
        self::expectExceptionMessage('`foo`');

        $root = self::makeContainer();
        $root->getBinder('bar')->bindSingleton('binding', static fn(): AttrScopeFoo => new AttrScopeFoo());

        $root->runScoped(static function (Container $fooScope): void {
            $fooScope->runScoped(static function (Container $container): void {
                $container->get('binding');
            }, name: 'bar');
        }, name: 'baz');
    }

    public function testAllParentNamedScopesNotContainsNeededScopeWithDisabledChecking(): void
    {
        $root = self::makeContainer(checkScope: false);
        $root->getBinder('bar')->bindSingleton('binding', static fn(): AttrScopeFoo => new AttrScopeFoo());

        $root->runScoped(static function (Container $fooScope): void {
            $fooScope->runScoped(static function (Container $container): void {
                self::assertInstanceOf(AttrScopeFoo::class, $container->get('binding'));
            }, name: 'bar');
        }, name: 'baz');
    }

    /**
     * The dependency has a constraint on the `foo` scope, but there is a binding in a bad `root` scope that has
     * high priority.
     * Requesting a dependency from a correct scope will be resolved from the `root` scope that leads to an exception.
     */
    #[Group('scrutinizer-ignore')]
    public function testRequestObjectFromValidScopeUsingFactoryFromWrongScope(): void
    {
        self::expectException(BadScopeException::class);
        self::expectExceptionMessage('`foo`');

        $root = self::makeContainer(checkScope: true);
        $root->bind('foo', self::makeFooScopeObject(...));

        $root->runScoped(static function (Container $c1): void {
            $c1->runScoped(static function (Container $c2): void {
                $c2->get('foo');
            }, name: 'foo');
        });
    }

    #[Group('scrutinizer-ignore')]
    public function testRequestObjectFromValidScopeUsingFactoryFromWrongScopeWithDisabledChecking(): void
    {
        $root = self::makeContainer(checkScope: false);
        $root->bind('foo', self::makeFooScopeObject(...));

        $root->runScoped(static function (Container $c1): void {
            $c1->runScoped(static function (Container $c2): void {
                self::assertInstanceOf(AttrScopeFoo::class, $c2->get('foo'));
            }, name: 'foo');
        });
    }

    /**
     * Request a dependency from an unnamed scope using alias and there is no any binding in valid scope for this alias.
     */
    #[Group('scrutinizer-ignore')]
    public function testNamedScopeUseFactoryInWrongParentScope(): void
    {
        self::expectException(BadScopeException::class);
        self::expectExceptionMessage('`foo`');

        $root = self::makeContainer();
        $root->bind('foo', self::makeFooScopeObject(...));

        $root->runScoped(static function (Container $c1): void {
            $c1->runScoped(static function (Container $c2): void {
                $c2->get('foo');
            });
        });
    }

    #[Group('scrutinizer-ignore')]
    public function testNamedScopeUseFactoryInWrongParentScopeWithDisabledChecking(): void
    {
        $root = self::makeContainer(checkScope: false);
        $root->bind('foo', self::makeFooScopeObject(...));

        $root->runScoped(static function (Container $c1): void {
            $c1->runScoped(static function (Container $c2): void {
                self::assertInstanceOf(AttrScopeFoo::class, $c2->get('foo'));
            });
        });
    }

    /**
     * In the parent hierarchy, the needed scope specified twice.
     * You can't create nested scopes with the same name.
     */
    #[Group('scrutinizer-ignore')]
    public function testNamedScopeDuplication(): void
    {
        self::expectException(NamedScopeDuplicationException::class);
        self::expectExceptionMessage('`root`');

        $root = self::makeContainer();

        try {
            $root->runScoped(static function (Container $c1): void {
                $c1->runScoped(static function (Container $c2): void {
                    $c2->runScoped(static function (Container $c3): void {
                        // do nothing
                    }, name: 'root');
                });
            });
        } catch (NamedScopeDuplicationException $e) {
            self::assertSame('root', $e->getScope());
            throw $e;
        }
    }

    /**
     * The {@see BasScopeException} must be thrown when trying to resolve a class with the {@see Scope} attribute
     * in a scope that is not specified in the attribute.
     */
    #[Group('scrutinizer-ignore')]
    public function testBadScopeException(): void
    {
        try {
            $root = self::makeContainer();
            $root->runScoped(static function (Container $c1): void {
                $c1->runScoped(static function (Container $c2): void {
                    $c2->get(AttrScopeFoo::class);
                });
            }, name: 'bar');

            self::fail(BadScopeException::class . ' should be thrown');
        } catch (BadScopeException $e) {
            self::assertSame('foo', $e->getScope());
        }
    }

    private static function makeFooScopeObject(): AttrScopeFoo
    {
        return new AttrScopeFoo();
    }
}
