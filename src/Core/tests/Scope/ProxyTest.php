<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Scope;

use Psr\Container\ContainerInterface;
use ReflectionParameter;
use Spiral\Core\Attribute\Proxy;
use Spiral\Core\Container;
use Spiral\Core\Container\InjectorInterface;
use Spiral\Core\Scope;
use Spiral\Tests\Core\Scope\Stub\Context;
use Spiral\Tests\Core\Scope\Stub\ContextInterface;
use Spiral\Tests\Core\Scope\Stub\FileLogger;
use Spiral\Tests\Core\Scope\Stub\KVLogger;
use Spiral\Tests\Core\Scope\Stub\LoggerInterface;
use Spiral\Tests\Core\Scope\Stub\ScopedProxyLoggerCarrier;
use Spiral\Tests\Core\Scope\Stub\ScopedProxyStdClass;

final class ProxyTest extends BaseTestCase
{
    public function testDifferentBindingsParallelScopes(): void
    {
        $root = new Container();

        // root scope
        $root->bindSingleton(ScopedProxyLoggerCarrier::class, ScopedProxyLoggerCarrier::class);
        $lc = $root->get(ScopedProxyLoggerCarrier::class);

        $root->getBinder('http')->bindSingleton(LoggerInterface::class, KVLogger::class);

        FiberHelper::runFiberSequence(
            static fn() => $root->runScope(
                new Scope(
                    name: 'http',
                    bindings: [
                        LoggerInterface::class => KVLogger::class,
                    ],
                ),
                static function (ScopedProxyLoggerCarrier $carrier, LoggerInterface $logger) use ($lc) {
                    // from the current `foo` scope
                    self::assertInstanceOf(KVLogger::class, $logger);

                    for ($i = 0; $i < 10; $i++) {
                        // because of proxy
                        self::assertNotInstanceOf(KVLogger::class, $carrier->getLogger());
                        self::assertSame('kv', $carrier->logger->getName());
                        self::assertSame($lc, $carrier);
                        \Fiber::suspend();
                    }
                }
            ),
            static fn() => $root->runScope(
                new Scope(
                    name: 'http',
                    bindings: [
                        LoggerInterface::class => FileLogger::class,
                    ],
                ),
                static function (ScopedProxyLoggerCarrier $carrier, LoggerInterface $logger) use ($lc) {
                    // from the current `foo` scope
                    self::assertInstanceOf(FileLogger::class, $logger);

                    for ($i = 0; $i < 10; $i++) {
                        // because of proxy
                        self::assertNotInstanceOf(FileLogger::class, $carrier->getLogger());
                        self::assertSame('file', $carrier->logger->getName());
                        self::assertSame($lc, $carrier);
                        \Fiber::suspend();
                    }
                }
            ),
        );
    }

    public function testResolveSameDependencyFromDifferentScopesSingleton(): void
    {
        $root = new Container();
        $root->getBinder('http')->bindSingleton(LoggerInterface::class, KVLogger::class);

        $root->runScope(new Scope(), static function (Container $c1) {
            $c1->runScope(
                new Scope(name: 'http'),
                static function (
                    ScopedProxyLoggerCarrier $carrier,
                    ScopedProxyLoggerCarrier $carrier2,
                    LoggerInterface $logger
                ) {
                    // from the current `foo` scope
                    self::assertInstanceOf(KVLogger::class, $logger);

                    // because of proxy
                    self::assertNotInstanceOf(KVLogger::class, $carrier->getLogger());

                    // because of proxy
                    self::assertSame('kv', $carrier->logger->getName());
                    self::assertNotSame($carrier2->logger, $carrier->logger, 'Different contexts');
                }
            );
        });
    }

    public function testResolveSameDependencyFromDifferentScopesNotSingleton(): void
    {
        $root = new Container();
        $root->getBinder('foo')->bind(LoggerInterface::class, KVLogger::class);

        $root->runScope(new Scope(), static function (Container $c1) {
            $c1->runScope(
                new Scope(name: 'foo'),
                static function (ScopedProxyLoggerCarrier $carrier, LoggerInterface $logger) {
                    // from the current `foo` scope
                    self::assertInstanceOf(KVLogger::class, $logger);

                    // because of proxy
                    self::assertNotInstanceOf(KVLogger::class, $carrier->getLogger());

                    // because of proxy
                    self::assertSame('kv', $carrier->logger->getName());
                }
            );
        });
    }

    public function testInjectorContext(): void
    {
        $root = new Container();
        $root->getBinder('foo')
            ->bind(
                ContextInterface::class,
                new \Spiral\Core\Config\Injectable(
                    new class implements InjectorInterface {
                        public function createInjection(\ReflectionClass $class, mixed $context = null): Context
                        {
                            return new Context($context);
                        }
                    }
                )
            );

        $root->runScope(new Scope(), static function (Container $c1) {
            $c1->runScope(new Scope(name: 'foo'), static function (Container $c, ContextInterface $param) {
                self::assertInstanceOf(ReflectionParameter::class, $param->value);
                self::assertSame('param', $param->value->getName());

                $get = $c->get(ContextInterface::class);
                self::assertNull($get->value);

                $get = $c->get(ContextInterface::class, 'custom');
                self::assertSame('custom', $get->value);

                /** @var ScopedProxyStdClass $proxy */
                $proxy = $c->get(ScopedProxyStdClass::class);
                self::assertInstanceOf(ContextInterface::class, $proxy->getContext(), 'Context was resolved');
                self::assertInstanceOf(
                    ReflectionParameter::class,
                    $proxy->getContext()->getValue(),
                    'Context was injected'
                );
                /** @see ScopedProxyStdClass::$context */
                self::assertSame('context', $proxy->getContext()->getValue()->getName());
            });
        });
    }

    public function testInjectorContextParallelScopes(): void
    {
        $root = new Container();
        $root->getBinder('foo')
            ->bind(
                ContextInterface::class,
                new \Spiral\Core\Config\Injectable(
                    new class implements InjectorInterface {
                        public function createInjection(\ReflectionClass $class, mixed $context = null): Context
                        {
                            return new Context($context);
                        }
                    }
                )
            );

        FiberHelper::runFiberSequence(
            static fn() => $root->runScope(new Scope(name: 'foo'), static function (ContextInterface $ctx) {
                for ($i = 0; $i < 10; $i++) {
                    self::assertInstanceOf(ReflectionParameter::class, $ctx->getValue(), 'Context injected');
                    self::assertSame('ctx', $ctx->getValue()->getName());
                    \Fiber::suspend();
                }
            }),
            static fn() => $root->runScope(new Scope(name: 'foo'), static function (ContextInterface $context) {
                for ($i = 0; $i < 10; $i++) {
                    self::assertInstanceOf(ReflectionParameter::class, $context->getValue(), 'Context injected');
                    self::assertSame('context', $context->getValue()->getName());
                    \Fiber::suspend();
                }
            }),
        );
    }

    public function testCurrentScopeContainer(): void
    {
        $root = new Container();
        $root->getBinder('http')->bindSingleton(LoggerInterface::class, KVLogger::class);

        $root->runScope(new Scope(), static function (#[Proxy] ContainerInterface $cp) use ($root) {
            $root->runScope(new Scope(name: 'http'), static function (ContainerInterface $c) use ($cp) {
                self::assertNotSame($c, $cp);
                self::assertSame($c, $cp->get(ContainerInterface::class));
                self::assertInstanceOf(KVLogger::class, $cp->get(LoggerInterface::class));
                self::assertSame($cp->get(LoggerInterface::class), $cp->get(LoggerInterface::class));
            });
        });
    }

    public function testProxyOutsideOfScope(): void
    {
        $root = new Container();
        $root->getBinder('http')->bindSingleton(LoggerInterface::class, KVLogger::class);

        $proxy = $root->runScope(new Scope(), static fn(#[Proxy] ContainerInterface $cp): ContainerInterface => $cp);

        self::expectExceptionMessage('Proxy is out of scope.');

        $proxy->get(LoggerInterface::class);
    }
}
