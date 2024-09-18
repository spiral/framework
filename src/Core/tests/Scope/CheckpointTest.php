<?php

declare(strict_types=1);

namespace Scope;

use Psr\Container\ContainerInterface;
use Spiral\Core\Container;
use Spiral\Core\Scope;
use Spiral\Core\ScopeInterface;
use Spiral\Tests\Core\Scope\BaseTestCase;
use Spiral\Tests\Core\Stub\EnumObject;
use Spiral\Tests\Core\Stub\EnumServiceWithDefault;

final class CheckpointTest extends BaseTestCase
{
    public function testWithFirstParentDefinitionAfterCheckpoint(): void
    {
        $root = new Container();
        $root->bind(EnumServiceWithDefault::class, new EnumServiceWithDefault(EnumObject::foo));
        $root->getBinder('foo')
            ->bind(EnumServiceWithDefault::class, fn() => new EnumServiceWithDefault(EnumObject::bar),);

        $result = $root->runScope(new Scope(name: 'foo'), function (ScopeInterface $c1) {
            return $c1->runScope(new Scope(name: 'bar', checkpoint: true), function (ContainerInterface $c2) {
                return $c2->get(EnumServiceWithDefault::class);
            });
        });

        self::assertInstanceOf(EnumServiceWithDefault::class, $result);
        self::assertSame(EnumObject::bar, $result->enum);
    }

    public function testWithSecondParentDefinitionAfterCheckpoint(): void
    {
        $root = new Container();
        $root->bind(EnumServiceWithDefault::class, new EnumServiceWithDefault(EnumObject::foo));

        $result = $root->runScope(new Scope(name: 'foo'), function (ScopeInterface $c1) {
            return $c1->runScope(new Scope(name: 'bar', checkpoint: true), function (ContainerInterface $c2) {
                return $c2->get(EnumServiceWithDefault::class);
            });
        });

        self::assertInstanceOf(EnumServiceWithDefault::class, $result);
        self::assertSame(EnumObject::foo, $result->enum);
    }

    public function testWithNoDefinitionInParentsCheckpoint(): void
    {
        $root = new Container();

        $result = $root->runScope(new Scope(name: 'foo'), function (ScopeInterface $c1) {
            return $c1->runScope(new Scope(name: 'bar', checkpoint: true), function (ContainerInterface $c2) {
                return $c2->get(EnumServiceWithDefault::class);
            });
        });

        self::assertInstanceOf(EnumServiceWithDefault::class, $result);
        self::assertSame(EnumObject::qux, $result->enum);
    }
}
