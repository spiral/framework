<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework;

use PHPUnit\Framework\Attributes\DataProvider;
use Spiral\App\Dispatcher\DispatcherWithCustomEnum;
use Spiral\App\Dispatcher\DispatcherWithoutScope;
use Spiral\App\Dispatcher\DispatcherWithScopeName;
use Spiral\App\Dispatcher\DispatcherWithStringScope;
use Spiral\App\Dispatcher\Scope;
use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\BootloaderRegistry;
use Spiral\Boot\Bootloader\BootloaderRegistryInterface;
use Spiral\Boot\Exception\BootException;
use Spiral\App\TestApp;
use Spiral\Core\Container;
use Spiral\Framework\Spiral;
use stdClass;

class KernelTest extends BaseTestCase
{
    public const MAKE_APP_ON_STARTUP = false;

    public function testBypassEnvironmentToConfig(): void
    {
        $this->initApp([
            'TEST_VALUE' => 'HELLO WORLD',
        ]);

        $this->assertConfigMatches('test', [
            'key' => 'HELLO WORLD',
        ]);
    }

    public function testGetEnv(): void
    {
        $this->initApp([
            'DEBUG' => true,
            'ENV' => 123,
        ]);

        $this->assertEnvironmentValueSame('ENV', 123);
    }

    public function testNoRootDirectory(): void
    {
        $this->expectException(BootException::class);

        $this->initApp();

        TestApp::create([], false)->run();
    }

    public function testCustomContainer(): void
    {
        $this->initApp();
        $container = new Container();
        $container->bind('foofoo', new stdClass());

        $app = TestApp::create([
            'root' => __DIR__.'/../',
        ], container: $container);

        self::assertSame($container, $app->getContainer());
        self::assertInstanceOf(stdClass::class, $app->getContainer()->get('foofoo'));
    }

    public function testRunningCallbackShouldBeFired(): void
    {
        $this->initApp();

        $callback1 = false;
        $callback2 = false;

        $kernel = TestApp::create(['root' => __DIR__.'/../']);
        $kernel->running(static function () use (&$callback1): void {
            $callback1 = true;
        });

        $kernel->running(static function () use (&$callback2): void {
            $callback2 = true;
        });

        $kernel->run();

        self::assertTrue($callback1);
        self::assertTrue($callback2);
    }

    public function testBootloaderRegistryShouldBeBoundAsSingleton(): void
    {
        $this->assertContainerBoundAsSingleton(BootloaderRegistryInterface::class, BootloaderRegistry::class);
    }

    public function testCustomBootloaderRegistry(): void
    {
        $registry = $this->createMock(BootloaderRegistryInterface::class);
        $container = new Container();
        $container->bindSingleton(BootloaderRegistryInterface::class, $registry);

        $kernel = TestApp::create(directories: ['root' => __DIR__. '/../'], container: $container);

        self::assertSame($registry, $kernel->getContainer()->get(BootloaderRegistryInterface::class));
    }

    public function testDispatcherWithoutNamedScope(): void
    {
        $this->beforeBooting(function (AbstractKernel $kernel): void {
            $kernel->addDispatcher(DispatcherWithoutScope::class);
        });

        $app = $this->makeApp();

        self::assertInstanceOf(DispatcherWithoutScope::class, $app->serve()['dispatcher']);
        self::assertSame('root', $app->serve()['scope']);

        self::assertTrue($app->getContainer()->has(DispatcherWithoutScope::class));
    }

    #[DataProvider('dispatchersDataProvider')]
    public function testDispatchersShouldBeBoundInCorrectScope(string $dispatcher, string $scope): void
    {
        $this->beforeBooting(function (AbstractKernel $kernel) use ($dispatcher): void {
            $kernel->addDispatcher($dispatcher);
        });

        $app = $this->makeApp();

        self::assertInstanceOf($dispatcher, $app->serve()['dispatcher']);
        self::assertSame($scope, $app->serve()['scope']);

        self::assertFalse($app->getContainer()->has($dispatcher));
    }

    public static function dispatchersDataProvider(): \Traversable
    {
        yield [DispatcherWithScopeName::class, Spiral::Console->value];
        yield [DispatcherWithCustomEnum::class, Scope::Custom->value];
        yield [DispatcherWithStringScope::class, 'test'];
    }
}
