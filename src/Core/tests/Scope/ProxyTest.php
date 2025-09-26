<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Scope;

use Psr\Container\ContainerInterface;
use Spiral\Core\Attribute\Proxy;
use Spiral\Core\Config\Proxy as ProxyConfig;
use Spiral\Core\Container;
use Spiral\Core\Container\InjectorInterface;
use Spiral\Core\ContainerScope;
use Spiral\Core\Exception\Container\RecursiveProxyException;
use Spiral\Core\Scope;
use Spiral\Tests\Core\Scope\Stub\Context;
use Spiral\Tests\Core\Scope\Stub\ContextInterface;
use Spiral\Tests\Core\Scope\Stub\DestroyableInterface;
use Spiral\Tests\Core\Scope\Stub\FileLogger;
use Spiral\Tests\Core\Scope\Stub\KVLogger;
use Spiral\Tests\Core\Scope\Stub\LoggerInterface;
use Spiral\Tests\Core\Scope\Stub\ScopedProxyLoggerCarrier;
use Spiral\Tests\Core\Scope\Stub\ScopedProxyStdClass;
use Spiral\Tests\Core\Scope\Stub\User;
use Spiral\Tests\Core\Scope\Stub\UserInterface;

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
            static fn(): mixed => $root->runScope(
                new Scope(
                    name: 'http',
                    bindings: [
                        LoggerInterface::class => KVLogger::class,
                    ],
                ),
                static function (ScopedProxyLoggerCarrier $carrier, LoggerInterface $logger) use ($lc): void {
                    // from the current `foo` scope
                    self::assertInstanceOf(KVLogger::class, $logger);

                    for ($i = 0; $i < 10; $i++) {
                        // because of proxy
                        self::assertNotInstanceOf(KVLogger::class, $carrier->getLogger());
                        self::assertSame('kv', $carrier->logger->getName());
                        self::assertSame($lc, $carrier);
                        \Fiber::suspend();
                    }
                },
            ),
            static fn(): mixed => $root->runScope(
                new Scope(
                    name: 'http',
                    bindings: [
                        LoggerInterface::class => FileLogger::class,
                    ],
                ),
                static function (ScopedProxyLoggerCarrier $carrier, LoggerInterface $logger) use ($lc): void {
                    // from the current `foo` scope
                    self::assertInstanceOf(FileLogger::class, $logger);

                    for ($i = 0; $i < 10; $i++) {
                        // because of proxy
                        self::assertNotInstanceOf(FileLogger::class, $carrier->getLogger());
                        self::assertSame('file', $carrier->logger->getName());
                        self::assertSame($lc, $carrier);
                        \Fiber::suspend();
                    }
                },
            ),
        );
    }

    public function testResolveSameDependencyFromDifferentScopesSingleton(): void
    {
        $root = new Container();
        $root->getBinder('http')->bindSingleton(LoggerInterface::class, KVLogger::class);

        $root->runScope(new Scope(), static function (Container $c1): void {
            $c1->runScope(
                new Scope(name: 'http'),
                static function (
                    ScopedProxyLoggerCarrier $carrier,
                    ScopedProxyLoggerCarrier $carrier2,
                    LoggerInterface $logger,
                ): void {
                    // from the current `foo` scope
                    self::assertInstanceOf(KVLogger::class, $logger);

                    // because of proxy
                    self::assertNotInstanceOf(KVLogger::class, $carrier->getLogger());

                    // because of proxy
                    self::assertSame('kv', $carrier->logger->getName());
                    self::assertNotSame($carrier2->logger, $carrier->logger, 'Different contexts');
                },
            );
        });
    }

    public function testResolveSameDependencyFromDifferentScopesNotSingleton(): void
    {
        $root = new Container();
        $root->getBinder('foo')->bind(LoggerInterface::class, KVLogger::class);

        $root->runScope(new Scope(), static function (Container $c1): void {
            $c1->runScope(
                new Scope(name: 'foo'),
                static function (ScopedProxyLoggerCarrier $carrier, LoggerInterface $logger): void {
                    // from the current `foo` scope
                    self::assertInstanceOf(KVLogger::class, $logger);

                    // because of proxy
                    self::assertNotInstanceOf(KVLogger::class, $carrier->getLogger());

                    // because of proxy
                    self::assertSame('kv', $carrier->logger->getName());
                },
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
                    },
                ),
            );

        $root->runScope(new Scope(), static function (Container $c1): void {
            $c1->runScope(new Scope(name: 'foo'), static function (Container $c, ContextInterface $param): void {
                self::assertInstanceOf(\ReflectionParameter::class, $param->value);
                self::assertSame('param', $param->value->getName());

                $get = $c->get(ContextInterface::class);
                self::assertNull($get->value);

                $get = $c->get(ContextInterface::class, 'custom');
                self::assertSame('custom', $get->value);

                /** @var ScopedProxyStdClass $proxy */
                $proxy = $c->get(ScopedProxyStdClass::class);
                self::assertInstanceOf(ContextInterface::class, $proxy->getContext(), 'Context was resolved');
                self::assertInstanceOf(
                    \ReflectionParameter::class,
                    $proxy->getContext()->getValue(),
                    'Context was injected',
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
                    },
                ),
            );

        FiberHelper::runFiberSequence(
            static fn(): mixed => $root->runScope(new Scope(name: 'foo'), static function (ContextInterface $ctx): void {
                for ($i = 0; $i < 10; $i++) {
                    self::assertInstanceOf(\ReflectionParameter::class, $ctx->getValue(), 'Context injected');
                    self::assertSame('ctx', $ctx->getValue()->getName());
                    \Fiber::suspend();
                }
            }),
            static fn(): mixed => $root->runScope(new Scope(name: 'foo'), static function (ContextInterface $context): void {
                for ($i = 0; $i < 10; $i++) {
                    self::assertInstanceOf(\ReflectionParameter::class, $context->getValue(), 'Context injected');
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

        $root->runScope(
            new Scope(),
            static function (#[Proxy] ContainerInterface $cp) use ($root): void {
                $root->runScope(new Scope(name: 'http'), static function (ContainerInterface $c) use ($cp): void {
                    self::assertNotSame($c, $cp);
                    self::assertSame($c, $cp->get(ContainerInterface::class));
                    self::assertInstanceOf(KVLogger::class, $cp->get(LoggerInterface::class));
                    self::assertSame($cp->get(LoggerInterface::class), $cp->get(LoggerInterface::class));
                });
            },
        );
    }

    public function testProxyDynamicScopeRunOutsideOfScope(): void
    {
        $root = new Container();
        $root->getBinder('http')->bindSingleton(LoggerInterface::class, KVLogger::class);

        $proxy = $root->runScope(
            new Scope(),
            static fn(#[Proxy] ContainerInterface $cp): ContainerInterface => $cp,
        );

        self::expectExceptionMessage('Proxy is out of scope.');

        $proxy->get(LoggerInterface::class);
    }

    public function testProxyDynamicScopeCreatedInNeededScopeRunOutsideOfScope(): void
    {
        $root = new Container();
        $root->getBinder('http')->bindSingleton(LoggerInterface::class, KVLogger::class);

        $proxy = $root->runScope(
            new Scope(name: 'http'),
            static fn(#[Proxy] ContainerInterface $cp): ContainerInterface => $cp,
        );

        self::expectExceptionMessage('Proxy is out of scope.');

        $proxy->get(LoggerInterface::class);
    }

    public function testDestroyMethod(): void
    {
        $root = new Container();
        $context = (object) ['destroyed' => false];
        $class = new class($context) implements DestroyableInterface {
            public function __construct(
                private readonly \stdClass $context,
            ) {}

            public function __destruct()
            {
                $this->context->destroyed = true;
            }
        };
        $root->bindSingleton(DestroyableInterface::class, $class);

        $proxy = $root->runScope(new Scope(), static fn(#[Proxy] DestroyableInterface $proxy): DestroyableInterface => $proxy);
        $weak = \WeakReference::create($proxy);
        unset($proxy);

        self::assertNull($weak->get());
        self::assertFalse($context->destroyed);
    }

    public function testImplementationWithWiderTypes(): void
    {
        $root = new Container();
        $root->getBinder('http')->bindSingleton(UserInterface::class, static fn(): User => new User('Foo'));
        $proxy = $root->runScope(new Scope(), static fn(#[Proxy] UserInterface $proxy): UserInterface => $proxy);

        $root->runScope(
            new Scope('http'),
            static function () use ($root, $proxy): void {
                self::assertSame('Foo', $proxy->getName());
                $proxy->setName(new class implements \Stringable {
                    public function __toString(): string
                    {
                        return 'Bar';
                    }
                });
                self::assertSame('Bar', $proxy->getName());
            },
        );
    }

    /**
     * Proxy gets a proxy of the same type.
     */
    public function testRecursiveProxyNotSingleton(): void
    {
        $root = new Container();
        $root->bind(UserInterface::class, new ProxyConfig(UserInterface::class));

        $this->expectException(RecursiveProxyException::class);
        $this->expectExceptionMessage(
            <<<MSG
                Recursive proxy detected for `Spiral\Tests\Core\Scope\Stub\UserInterface`.
                Binding scope: `root`.
                Calling scope: `root.null`.
                MSG,
        );

        $root->runScope(
            new Scope(),
            static fn(#[Proxy] UserInterface $user): string => $user->getName(),
        );
    }

    /**
     * Proxy gets a proxy of the same type as a singleton.
     */
    public function testRecursiveProxySingleton(): void
    {
        $root = new Container();
        $root->bind(UserInterface::class, new ProxyConfig(UserInterface::class, singleton: true));

        $this->expectException(RecursiveProxyException::class);
        $this->expectExceptionMessage(
            <<<MSG
                Recursive proxy detected for `Spiral\Tests\Core\Scope\Stub\UserInterface`.
                Calling scope: `root.null`.
                MSG,
        );

        $root->runScope(
            new Scope(),
            static fn(#[Proxy] UserInterface $user): string => $user->getName(),
        );
    }

    /**
     * The {@see ContainerScope::runScope} ignores Container Proxy to avoid recursion.
     */
    public function testProxyIntoContainerScope(): void
    {
        $root = new Container();

        $root->runScope(
            new Scope(),
            static function (#[Proxy] ContainerInterface $proxy, ContainerInterface $scoped): void {
                self::assertNotSame($scoped, $proxy);
                ContainerScope::runScope($proxy, static function (ContainerInterface $passed) use ($proxy, $scoped): void {
                    self::assertNotSame($passed, $proxy);
                    self::assertSame($scoped, ContainerScope::getContainer());
                });
            },
        );
    }

    public function testProxyFallbackFactory(): void
    {
        $root = new Container();
        $root->bind(UserInterface::class, new ProxyConfig(
            interface: UserInterface::class,
            fallbackFactory: static fn(): UserInterface => new User('Foo'),
        ));

        $name = $root->runScope(
            new Scope(),
            static fn(#[Proxy] UserInterface $user): string => $user->getName(),
        );

        self::assertSame('Foo', $name);
    }

    public function testHasAliasOutOfScopeWithoutFallback(): void
    {
        $root = new Container();
        $root->bind('foo', new ProxyConfig(
            interface: UserInterface::class,
        ));

        $result = $root->runScope(
            new Scope(),
            static fn(ContainerInterface $c): bool => $c->has('foo'),
        );


        self::assertFalse($result);
    }

    public function testHasOutOfScopeWithoutFallback(): void
    {
        $root = new Container();
        $root->bind(UserInterface::class, new ProxyConfig(
            interface: UserInterface::class,
        ));

        $result = $root->runScope(
            new Scope(),
            static fn(ContainerInterface $c): bool => $c->has(UserInterface::class),
        );


        self::assertFalse($result);
    }

    public function testHasOutOfScopeWithFallback(): void
    {
        $root = new Container();
        $root->bind(UserInterface::class, new ProxyConfig(
            interface: UserInterface::class,
            fallbackFactory: static fn(): UserInterface => new User('Foo'),
        ));

        $result = $root->runScope(
            new Scope(),
            static fn(ContainerInterface $c): bool => $c->has(UserInterface::class),
        );


        self::assertTrue($result);
    }

    public function testHasOutOfScopeWithFallbackException(): void
    {
        $root = new Container();
        $root->bind(UserInterface::class, new ProxyConfig(
            interface: UserInterface::class,
            fallbackFactory: static function (): never {
                throw new \RuntimeException('Nope');
            },
        ));

        $result = $root->runScope(
            new Scope(),
            static fn(ContainerInterface $c): bool => $c->has(UserInterface::class),
        );


        self::assertFalse($result);
    }

    /*
    // Proxy::$attachContainer=true tests

    public function testProxyStaticScopeRunOutsideOfScope(): void
    {
        $root = new Container();
        $root->getBinder('http')->bindSingleton(LoggerInterface::class, KVLogger::class);

        $proxy = $root->runScope(
            new Scope(),
            static fn(#[Proxy(attachContainer: true)] ContainerInterface $cp): ContainerInterface => $cp,
        );

        // Because:
        // 1. Proxy created in a wrong scope (`http` needed)
        // 2. The scope where the Proxy has been created was destroyed
        self::expectException(\Spiral\Core\Exception\Container\ContainerException::class);
        self::expectExceptionMessageMatches('/Unable to resolve/i');

        $proxy->get(LoggerInterface::class);
    }

    public function testProxyStaticScopeCreatedInNeededScopeRunOutsideOfScope(): void
    {
        $root = new Container();
        $root->getBinder('http')->bindSingleton(LoggerInterface::class, KVLogger::class);

        $proxy = $root->runScope(
            new Scope(name: 'http'),
            static fn(#[Proxy(attachContainer: true)] ContainerInterface $cp): ContainerInterface => $cp,
        );

        // Because of the `http` scope has been destroyed
        self::expectException(\Spiral\Core\Exception\Container\ContainerException::class);
        self::expectExceptionMessageMatches('/Unable to resolve/i');
        self::assertInstanceOf(KVLogger::class, $proxy->get(LoggerInterface::class));
    }

    public function testStaticScopeProxyInsideAnotherScope(): void
    {
        $root = new Container();
        $root->getBinder('foo')->bindSingleton(LoggerInterface::class, KVLogger::class);
        $root->getBinder('bar')->bindSingleton(LoggerInterface::class, FileLogger::class);

        $root->runScope(
            new Scope(name: 'foo'),
            static function (#[Proxy(attachContainer: true)] LoggerInterface $fooProxy, ContainerInterface $c) {
                $c->runScope(
                    new Scope(name: 'bar'),
                    static function (#[Proxy(attachContainer: true)] LoggerInterface $barProxy, ContainerInterface $c) use ($fooProxy) {
                        $c->runScope(
                            new Scope(),
                            static function (ContainerInterface $c) use ($fooProxy, $barProxy) {
                                self::assertSame('kv', $fooProxy->getName());
                                self::assertSame('file', $barProxy->getName());
                            },
                        );
                    },
                );
            },
        );
    } */
}
