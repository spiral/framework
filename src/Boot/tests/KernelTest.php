<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot;

use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Boot\BootloadManager\StrategyBasedBootloadManager;
use Spiral\Boot\BootloadManager\DefaultInvokerStrategy;
use Spiral\Boot\BootloadManager\Initializer;
use Spiral\Boot\BootloadManager\InitializerInterface;
use Spiral\Boot\BootloadManager\InvokerStrategyInterface;
use Spiral\Boot\BootloadManagerInterface;
use Spiral\Boot\DispatcherInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Boot\Event\Bootstrapped;
use Spiral\Boot\Event\DispatcherFound;
use Spiral\Boot\Event\DispatcherNotFound;
use Spiral\Boot\Event\Serving;
use Spiral\Boot\Exception\BootException;
use Spiral\Core\Container;
use Spiral\Core\Container\Autowire;
use Spiral\Tests\Boot\Fixtures\CustomInitializer;
use Spiral\Tests\Boot\Fixtures\CustomInvokerStrategy;
use Spiral\Tests\Boot\Fixtures\TestCore;
use Throwable;

class KernelTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function testKernelException(): void
    {
        $this->expectException(BootException::class);

        $kernel = TestCore::create(['root' => __DIR__])->run();

        $kernel->serve();
    }

    /**
     * @throws Throwable
     */
    public function testDispatcher(): void
    {
        $kernel = TestCore::create(['root' => __DIR__])->run();

        $d = new class() implements DispatcherInterface {
            public static function canServe(EnvironmentInterface $env): bool
            {
                return true;
            }

            public function serve(): bool
            {
                return true;
            }
        };
        $kernel->addDispatcher($d);

        $this->assertTrue($kernel->serve());
    }

    public function testDispatcherNonStaticServe(): void
    {
        $kernel = TestCore::create(['root' => __DIR__])->run();

        $d = new class() implements DispatcherInterface {
            public function canServe(): bool
            {
                return true;
            }

            public function serve(): bool
            {
                return true;
            }
        };
        $kernel->addDispatcher($d);

        $this->assertTrue($kernel->serve());
    }

    /**
     * @throws Throwable
     */
    public function testDispatcherReturnCode(): void
    {
        $kernel = TestCore::create(['root' => __DIR__])->run();

        $d = new class() implements DispatcherInterface {
            public static function canServe(EnvironmentInterface $env): bool
            {
                return true;
            }

            public function serve(): int
            {
                return 1;
            }
        };
        $kernel->addDispatcher($d);

        $result = $kernel->serve();
        $this->assertSame(1, $result);
    }

    /**
     * @throws Throwable
     */
    public function testEnv(): void
    {
        $kernel = TestCore::create(['root' => __DIR__])->run();

        $this->assertSame(
            'VALUE',
            $kernel->getContainer()->get(EnvironmentInterface::class)->get('INTERNAL')
        );
    }

    public function testBootingCallbacks()
    {
        $kernel = TestCore::create(['root' => __DIR__]);

        $kernel->booting(static function (TestCore $core) {
            $core->getContainer()->bind('abc', 'foo');
        });

        $kernel->booting(static function (TestCore $core) {
            $core->getContainer()->bind('bcd', 'foo');
        });

        $kernel->booted( static function (TestCore $core) {
            $core->getContainer()->bind('cde', 'foo');
        });

        $kernel->booted( static function (TestCore $core) {
            $core->getContainer()->bind('def', 'foo');
        });

        $kernel->run();

        $this->assertTrue($kernel->getContainer()->has('abc'));
        $this->assertTrue($kernel->getContainer()->has('bcd'));
        $this->assertTrue($kernel->getContainer()->has('cde'));
        $this->assertTrue($kernel->getContainer()->has('def'));
        $this->assertTrue($kernel->getContainer()->has('efg'));
        $this->assertFalse($kernel->getContainer()->has('fgh'));
        $this->assertFalse($kernel->getContainer()->has('ghi'));
        $this->assertTrue($kernel->getContainer()->has('hij'));
        $this->assertTrue($kernel->getContainer()->has('ijk'));
        $this->assertTrue($kernel->getContainer()->has('jkl'));
        $this->assertFalse($kernel->getContainer()->has('klm'));
        $this->assertTrue($kernel->getContainer()->has('lmn'));
        $this->assertTrue($kernel->getContainer()->has('mno'));
    }

    public function testEventsShouldBeDispatched(): void
    {
        $testDispatcher = new class implements DispatcherInterface {
            public static function canServe(EnvironmentInterface $env): bool
            {
                return true;
            }

            public function serve(): void
            {
            }
        };

        $container = new Container();
        $kernel = TestCore::create(directories: ['root' => __DIR__,], container: $container)
            ->addDispatcher($testDispatcher);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::exactly(3))
            ->method('dispatch')
            ->with($this->logicalOr(
                new Bootstrapped($kernel),
                new Serving(),
                new DispatcherFound($testDispatcher),
            ));

        $container->bind(EventDispatcherInterface::class, $dispatcher);

        $kernel->run()->serve();
    }

    public function testDispatcherNotFoundEventShouldBeDispatched(): void
    {
        $container = new Container();
        $kernel = TestCore::create(directories: ['root' => __DIR__,], container: $container);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::exactly(3))
            ->method('dispatch')
            ->with($this->logicalOr(
                new Bootstrapped($kernel),
                new Serving(),
                new DispatcherNotFound(),
            ));

        $container->bind(EventDispatcherInterface::class, $dispatcher);

        $this->expectException(BootException::class);

        $kernel->run()->serve();
    }

    public function testDefaultInitializerShouldBeBound(): void
    {
        $container = new Container();

        TestCore::create(directories: ['root' => __DIR__], container: $container);

        $this->assertTrue($container->has(InitializerInterface::class));
        $this->assertInstanceOf(Initializer::class, $container->get(InitializerInterface::class));
    }

    public function testCustomInitializerShouldBeBound(): void
    {
        $container = new Container();
        $container->bind(InitializerInterface::class, CustomInitializer::class);

        TestCore::create(directories: ['root' => __DIR__], container: $container);

        $this->assertTrue($container->has(InitializerInterface::class));
        $this->assertInstanceOf(CustomInitializer::class, $container->get(InitializerInterface::class));
    }

    public function testDefaultInvokerStrategyShouldBeBound(): void
    {
        $container = new Container();

        TestCore::create(directories: ['root' => __DIR__], container: $container);

        $this->assertTrue($container->has(InvokerStrategyInterface::class));
        $this->assertInstanceOf(DefaultInvokerStrategy::class, $container->get(InvokerStrategyInterface::class));
    }

    public function testCustomInvokerStrategyShouldBeBound(): void
    {
        $container = new Container();
        $container->bind(InvokerStrategyInterface::class, CustomInvokerStrategy::class);

        TestCore::create(directories: ['root' => __DIR__], container: $container);

        $this->assertTrue($container->has(InvokerStrategyInterface::class));
        $this->assertInstanceOf(CustomInvokerStrategy::class, $container->get(InvokerStrategyInterface::class));
    }

    public function testResolveBootloadManagerFromAutowire(): void
    {
        $container = new Container();

        TestCore::create(
            directories: ['root' => __DIR__],
            container: $container,
            bootloadManager: new Autowire(StrategyBasedBootloadManager::class, [
                'invoker' => new CustomInvokerStrategy()
            ])
        );

        /** @var BootloadManagerInterface $manager */
        $manager = $container->get(BootloadManagerInterface::class);

        $this->assertInstanceOf(
            CustomInvokerStrategy::class,
            (new \ReflectionProperty($manager, 'invoker'))->getValue($manager)
        );
    }
}
