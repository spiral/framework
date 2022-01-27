<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Boot;

use PHPUnit\Framework\TestCase;
use Spiral\Boot\DispatcherInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Boot\Exception\BootException;
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

        $kernel = TestCore::init([
            'root' => __DIR__,
        ]);

        $kernel->serve();
    }

    /**
     * @throws Throwable
     */
    public function testDispatcher(): void
    {
        $kernel = TestCore::init([
            'root' => __DIR__,
        ]);

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
        $kernel = TestCore::init([
            'root' => __DIR__,
        ]);

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
        $kernel = TestCore::init([
            'root' => __DIR__,
        ]);

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
    }
}
