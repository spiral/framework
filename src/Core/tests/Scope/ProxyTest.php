<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Scope;

use Spiral\Core\Container;
use Spiral\Tests\Core\Scope\Stub\KVLogger;
use Spiral\Tests\Core\Scope\Stub\LoggerInterface;
use Spiral\Tests\Core\Scope\Stub\ScopedProxyLoggerCarrier;

final class ProxyTest extends BaseTestCase
{
    /**
     */
    public function testResolveSameDependencyFromDifferentScopes(): void
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

    public function testResolveSameDependencyFromDifferentScopes2(): void
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
