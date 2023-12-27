<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Scope;

use Spiral\Core\Container;
use Spiral\Core\Scope;
use Spiral\Tests\Core\Scope\Stub\FileLogger;
use Spiral\Tests\Core\Scope\Stub\KVLogger;
use Spiral\Tests\Core\Scope\Stub\LoggerInterface;
use Spiral\Tests\Core\Scope\Stub\ScopedProxyLoggerCarrier;

// todo: add test with proxy and injector
final class ProxyTest extends BaseTestCase
{
    public function testDifferentBindingsParallelScopes(): void
    {
        $root = new Container();

        // root scope
        $root->bindSingleton(ScopedProxyLoggerCarrier::class, ScopedProxyLoggerCarrier::class);
        $lc = $root->get(ScopedProxyLoggerCarrier::class);

        $root->getBinder('http')->bindSingleton(LoggerInterface::class, KVLogger::class);

        // todo add fibers
        FiberHelper::runFiberSequence(
            static fn() => $root->runScope(new Scope(
                name: 'http',
                bindings: [
                    LoggerInterface::class => KVLogger::class,
                ],
            ), static function (ScopedProxyLoggerCarrier $carrier, LoggerInterface $logger) use ($lc) {
                // from the current `foo` scope
                self::assertInstanceOf(KVLogger::class, $logger);

                for ($i = 0; $i < 10; $i++) {
                    // because of proxy
                    self::assertNotInstanceOf(KVLogger::class, $carrier->getLogger());
                    self::assertSame('kv', $carrier->logger->getName());
                    self::assertSame($lc, $carrier);
                    \Fiber::suspend();
                }
            }),
            static fn() => $root->runScope(new Scope(
                name: 'http',
                bindings: [
                    LoggerInterface::class => FileLogger::class,
                ],
            ), static function (ScopedProxyLoggerCarrier $carrier, LoggerInterface $logger) use ($lc) {
                // from the current `foo` scope
                self::assertInstanceOf(FileLogger::class, $logger);

                for ($i = 0; $i < 10; $i++) {
                    // because of proxy
                    self::assertNotInstanceOf(FileLogger::class, $carrier->getLogger());
                    self::assertSame('file', $carrier->logger->getName());
                    self::assertSame($lc, $carrier);
                    \Fiber::suspend();
                }
            }),
        );

    }

    public function testResolveSameDependencyFromDifferentScopesSingleton(): void
    {
        $root = new Container();
        $root->getBinder('http')->bindSingleton(LoggerInterface::class, KVLogger::class);

        $root->runScoped(static function (Container $c1) {
            $c1->runScoped(static function (ScopedProxyLoggerCarrier $carrier, ScopedProxyLoggerCarrier $carrier2, LoggerInterface $logger) {
                // from the current `foo` scope
                self::assertInstanceOf(KVLogger::class, $logger);

                // because of proxy
                self::assertNotInstanceOf(KVLogger::class, $carrier->getLogger());

                // because of proxy
                self::assertSame('kv', $carrier->logger->getName());
                self::assertSame($carrier2->logger, $carrier->logger);
            }, name: 'http');
        });
    }

    public function testResolveSameDependencyFromDifferentScopesNotSingleton(): void
    {
        $root = new Container();
        $root->getBinder('foo')->bind(LoggerInterface::class, KVLogger::class);

        $root->runScoped(static function (Container $c1) {
            $c1->runScoped(static function (ScopedProxyLoggerCarrier $carrier, LoggerInterface $logger) {
                // from the current `foo` scope
                self::assertInstanceOf(KVLogger::class, $logger);

                // because of proxy
                self::assertNotInstanceOf(KVLogger::class, $carrier->getLogger());

                // because of proxy
                self::assertSame('kv', $carrier->logger->getName());
            }, name: 'foo');
        });
    }
}
