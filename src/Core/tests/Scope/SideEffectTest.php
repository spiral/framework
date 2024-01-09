<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Scope;

use Spiral\Core\Container;
use Spiral\Core\Scope;
use Spiral\Tests\Core\Scope\Stub\Factory;
use Spiral\Tests\Core\Scope\Stub\FileLogger;
use Spiral\Tests\Core\Scope\Stub\KVLogger;
use Spiral\Tests\Core\Scope\Stub\LoggerCarrier;
use Spiral\Tests\Core\Scope\Stub\LoggerInterface;

final class SideEffectTest extends BaseTestCase
{
    /**
     * When a dependency is resolving from parent then all its dependencies are resolved from parent too.
     * But the next child dependency will be resolved from the child container.
     */
    public function testResolveSameDependencyFromDifferentScopes(): void
    {
        $root = new Container();
        $root->bind(LoggerInterface::class, KVLogger::class);

        $root->runScope(new Scope(), static function (Container $c1) {
            $c1->bind(LoggerInterface::class, FileLogger::class);

            $c1->runScope(new Scope(), static function (LoggerCarrier $carrier, LoggerInterface $logger) {
                // from the $root container
                self::assertInstanceOf(KVLogger::class, $carrier->logger);
                // from the $c1 container
                self::assertInstanceOf(FileLogger::class, $logger);
            });
        });
    }

    public function testFactory(): void
    {
        $root = new Container();
        $root->bind(LoggerInterface::class, KVLogger::class);

        $root->runScope(new Scope(), static function (Container $c1) {
            $c1->bind(LoggerInterface::class, FileLogger::class);

            self::assertInstanceOf(
                LoggerInterface::class,
                $c1->get(Factory::class)->make(LoggerInterface::class),
            );
        });
    }
}
