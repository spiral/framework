<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Scope;

use Spiral\Core\Attribute\Scope;
use Spiral\Core\Container;
use Spiral\Core\Exception\Container\NotFoundException;
use Spiral\Core\Exception\Scope\BadScopeException;
use Spiral\Core\Exception\Scope\NamedScopeDuplicationException;
use Spiral\Tests\Core\Scope\Stub\AttrScopeFoo;
use Spiral\Tests\Core\Scope\Stub\AttrScopeFooSingleton;

final class ScopeAttributeTest extends BaseTest
{
    /**
     * Just try to make a dependency with a wrong scope.
     */
    public function testBadScope(): void
    {
        self::expectException(BadScopeException::class);

        $root = new Container();
        $root->make(AttrScopeFooSingleton::class);
    }

    /**
     * Test that a dependency are resolved in the scope specified in the {@see Scope} attribute.
     */
    public function testNamedScopeResolveFromRootInNullScope(): void
    {
        $root = new Container();

        $root->scope(static function (Container $c1) {
            $c1->scope(static function (Container $c2) use ($c1) {
                $obj1 = $c1->get(AttrScopeFooSingleton::class);
                $obj2 = $c2->get(AttrScopeFooSingleton::class);

                self::assertSame($obj1, $obj2);
            });
        }, name: 'foo');
    }

    /**
     * Request a dependency from a correct scope using alias but there is no any binding for this alias in the scope.
     * The binding can be in the parent scope, but it doesn't matter.
     */
    public function testRequestObjectFromValidScopeUsingFactoryFromWrongScope(): void
    {
        self::expectException(NotFoundException::class);
        self::expectExceptionMessage('`foo`');

        $root = new Container();
        $root->bind('foo', self::makeFooScopeObject(...));

        $root->scope(static function (Container $c1) {
            $c1->scope(static function (Container $c2) {
                $c2->get('foo');
            }, name: 'foo');
        });
    }

    /**
     * Request a dependency from an unnamed scope using alias and there is no any binding in valid scope for this alias.
     */
    public function testNamedScopeUseFactoryInWrongParentScope(): void
    {
        self::expectException(BadScopeException::class);
        self::expectExceptionMessage('`foo`');

        $root = new Container();
        $root->bind('foo', self::makeFooScopeObject(...));

        $root->scope(static function (Container $c1) {
            $c1->scope(static function (Container $c2) {
                $c2->get('foo');
            });
        });
    }

    /**
     * In the parent hierarchy, the needed scope specified twice.
     * You can't create nested scopes with the same name.
     */
    public function testNamedScopeDuplication(): void
    {
        self::expectException(NamedScopeDuplicationException::class);
        self::expectExceptionMessage('`root`');

        $root = new Container();

        try {
            $root->scope(static function (Container $c1) {
                $c1->scope(static function (Container $c2) {
                    $c2->scope(static function (Container $c3) {
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
    public function testBadScopeException(): void
    {
        self::expectException(BadScopeException::class);

        try {
            $root = new Container();
            $root->scope(static function (Container $c1) {
                $c1->scope(static function (Container $c2) {
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
