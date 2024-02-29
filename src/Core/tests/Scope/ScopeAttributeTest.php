<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Scope;

use PHPUnit\Framework\Attributes\Group;
use Spiral\Core\Attribute\Scope;
use Spiral\Core\Container;
use Spiral\Core\Exception\Container\NotFoundException;
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
        $this->assertInstanceOf(AttrScopeFooSingleton::class, $root->make(AttrScopeFooSingleton::class));
    }

    /**
     * Test that a dependency are resolved in the scope specified in the {@see Scope} attribute.
     */
    public function testNamedScopeResolveFromRootInNullScope(): void
    {
        $root = self::makeContainer();

        $root->runScoped(static function (Container $c1) {
            $c1->runScoped(static function (Container $c2) use ($c1) {
                $obj1 = $c1->get(AttrScopeFooSingleton::class);
                $obj2 = $c2->get(AttrScopeFooSingleton::class);

                self::assertSame($obj1, $obj2);
            });
        }, name: 'foo');
    }

    public function testNamedScopeResolveFromParentScope(): void
    {
        $root = self::makeContainer();
        $root->getBinder('bar')->bindSingleton('binding', static fn () => new AttrScopeFoo());

        $root->runScoped(static function (Container $fooScope) {
            $fooScope->runScoped(static function (Container $container) {
                self::assertInstanceOf(AttrScopeFoo::class, $container->get('binding'));
            }, name: 'bar');
        }, name: 'foo');
    }

    public function testBadScopeExceptionAllParentNamedScopesNotContainsNeededScope(): void
    {
        self::expectException(BadScopeException::class);
        self::expectExceptionMessage('`foo`');

        $root = self::makeContainer();
        $root->getBinder('bar')->bindSingleton('binding', static fn () => new AttrScopeFoo());

        $root->runScoped(static function (Container $fooScope) {
            $fooScope->runScoped(static function (Container $container) {
                $container->get('binding');
            }, name: 'bar');
        }, name: 'baz');
    }

    public function testAllParentNamedScopesNotContainsNeededScopeWithDisabledChecking(): void
    {
        $root = self::makeContainer(checkScope: false);
        $root->getBinder('bar')->bindSingleton('binding', static fn () => new AttrScopeFoo());

        $root->runScoped(static function (Container $fooScope) {
            $fooScope->runScoped(static function (Container $container) {
                self::assertInstanceOf(AttrScopeFoo::class, $container->get('binding'));
            }, name: 'bar');
        }, name: 'baz');
    }

    /**
     * Request a dependency from a correct scope using alias but there is no any binding for this alias in the scope.
     * The binding can be in the parent scope, but it doesn't matter.
     */
    #[Group('scrutinizer-ignore')]
    public function testRequestObjectFromValidScopeUsingFactoryFromWrongScope(): void
    {
        self::expectException(NotFoundException::class);
        self::expectExceptionMessage('`foo`');

        $root = self::makeContainer();
        $root->bind('foo', self::makeFooScopeObject(...));

        $root->runScoped(static function (Container $c1) {
            $c1->runScoped(static function (Container $c2) {
                $c2->get('foo');
            }, name: 'foo');
        });
    }

    #[Group('scrutinizer-ignore')]
    public function testRequestObjectFromValidScopeUsingFactoryFromWrongScopeWithDisabledChecking(): void
    {
        $root = self::makeContainer(checkScope: false);
        $root->bind('foo', self::makeFooScopeObject(...));

        $root->runScoped(static function (Container $c1) {
            $c1->runScoped(static function (Container $c2) {
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

        $root->runScoped(static function (Container $c1) {
            $c1->runScoped(static function (Container $c2) {
                $c2->get('foo');
            });
        });
    }

    #[Group('scrutinizer-ignore')]
    public function testNamedScopeUseFactoryInWrongParentScopeWithDisabledChecking(): void
    {
        $root = self::makeContainer(checkScope: false);
        $root->bind('foo', self::makeFooScopeObject(...));

        $root->runScoped(static function (Container $c1) {
            $c1->runScoped(static function (Container $c2) {
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
            $root->runScoped(static function (Container $c1) {
                $c1->runScoped(static function (Container $c2) {
                    $c2->runScoped(static function (Container $c3) {
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
        self::expectException(BadScopeException::class);

        try {
            $root = self::makeContainer();
            $root->runScoped(static function (Container $c1) {
                $c1->runScoped(static function (Container $c2) {
                    $c2->get(AttrScopeFoo::class);
                });
            }, name: 'bar');
        } catch (BadScopeException $e) {
            self::assertSame('foo', $e->getScope());
            throw $e;
        }
    }

    private static function makeFooScopeObject(): AttrScopeFoo
    {
        return new AttrScopeFoo();
    }
}
