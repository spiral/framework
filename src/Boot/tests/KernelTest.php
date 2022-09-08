<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot;

use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Boot\DispatcherInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Boot\Event\Bootstrapped;
use Spiral\Boot\Event\DispatcherFound;
use Spiral\Boot\Event\DispatcherNotFound;
use Spiral\Boot\Event\Serving;
use Spiral\Boot\Exception\BootException;
use Spiral\Core\Container;
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

        $kernel = TestCore::create([
            'root' => __DIR__,
        ])->run();

        $kernel->serve();
    }

    /**
     * @throws Throwable
     */
    public function testDispatcher(): void
    {
        $kernel = TestCore::create([
            'root' => __DIR__,
        ])->run();

        $d = new class() implements DispatcherInterface {
            public $fired = false;

            public function canServe(): bool
            {
                return true;
            }

            public function serve(): void
            {
                $this->fired = true;
            }
        };
        $kernel->addDispatcher($d);
        $this->assertFalse($d->fired);

        $kernel->serve();
        $this->assertTrue($d->fired);
    }

    /**
     * @throws Throwable
     */
    public function testDispatcherReturnCode(): void
    {
        $kernel = TestCore::create([
            'root' => __DIR__,
        ])->run();

        $d = new class() implements DispatcherInterface {
            public function canServe(): bool
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
        $kernel = TestCore::create([
            'root' => __DIR__,
        ])->run();

        $this->assertSame(
            'VALUE',
            $kernel->getContainer()->get(EnvironmentInterface::class)->get('INTERNAL')
        );
    }

    public function testBootingCallbacks()
    {
        $kernel = TestCore::create([
            'root' => __DIR__,
        ]);

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


        $this->assertInstanceOf(
            EnvironmentInterface::class,
            $kernel->getContainer()->get(EnvironmentInterface::class)
        );
    }

    public function testAppBootingCallbacks()
    {
        $kernel = TestCore::create([
            'root' => __DIR__,
        ]);

        $kernel->appBooting(static function (TestCore $core) {
            $core->getContainer()->bind('abc', 'foo');
        });

        $kernel->appBooting(static function (TestCore $core) {
            $core->getContainer()->bind('bcd', 'foo');
        });

        $kernel->appBooted( static function (TestCore $core) {
            $core->getContainer()->bind('cde', 'foo');
        });

        $kernel->appBooted( static function (TestCore $core) {
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


        $this->assertInstanceOf(
            EnvironmentInterface::class,
            $kernel->getContainer()->get(EnvironmentInterface::class)
        );
    }

    public function testEventsShouldBeDispatched(): void
    {
        $testDispatcher = new class implements DispatcherInterface {
            public function canServe(): bool
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
}
