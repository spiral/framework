<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Core\ContainerScope;
use Spiral\Core\Exception\ScopeException;
use Spiral\Prototype\Exception\PrototypeException;
use Spiral\Prototype\PrototypeRegistry;
use Spiral\Tests\Prototype\Fixtures\TestClass;

class TraitTest extends TestCase
{
    public function testNoScope(): void
    {
        $this->expectException(ScopeException::class);

        $t = new TestClass();
        $t->getTest();
    }

    public function testNoScopeBound(): void
    {
        $this->expectException(ScopeException::class);

        $t = new TestClass();

        $c = new Container();

        ContainerScope::runScope($c, static function () use ($t): void {
            $t->getTest();
        });
    }

    public function testCascade(): void
    {
        $this->expectException(PrototypeException::class);

        $t = new TestClass();
        $c = new Container();
        $c->bindSingleton(PrototypeRegistry::class, $p = new PrototypeRegistry($c));
        $p->bindProperty('testClass', 'Invalid');

        ContainerScope::runScope($c, static function () use ($t): void {
            $t->getTest();
        });
    }

    public function testOK(): void
    {
        $t = new TestClass();
        $c = new Container();
        $c->bindSingleton(PrototypeRegistry::class, $p = new PrototypeRegistry($c));
        $c->bindSingleton(TestClass::class, $t);
        $p->bindProperty('testClass', TestClass::class);

        $r = ContainerScope::runScope($c, static fn() => $t->getTest());

        self::assertSame($t, $r);
    }
}
