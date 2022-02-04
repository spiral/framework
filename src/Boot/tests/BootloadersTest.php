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
use Spiral\Boot\BootloadManager;
use Spiral\Tests\Boot\Fixtures\BootloaderA;
use Spiral\Tests\Boot\Fixtures\BootloaderB;
use Spiral\Tests\Boot\Fixtures\SampleBoot;
use Spiral\Tests\Boot\Fixtures\SampleBootWithStarted;
use Spiral\Tests\Boot\Fixtures\SampleClass;
use Spiral\Core\Container;

class BootloadersTest extends TestCase
{
    public function testSchemaLoading(): void
    {
        $container = new Container();

        $bootloader = new BootloadManager($container);
        $bootloader->bootload($classes = [
            SampleClass::class,
            SampleBootWithStarted::class,
            SampleBoot::class,
        ], [
            static function(Container $container) {
                $container->bind('efg', new SampleBoot());
            }
        ], [
            static function(Container $container) {
                $container->bind('ghi', new SampleBoot());
            }
        ]);

        $this->assertTrue($container->has('abc'));
        $this->assertTrue($container->hasInstance('cde'));
        $this->assertTrue($container->hasInstance('def'));
        $this->assertTrue($container->hasInstance('efg'));
        $this->assertTrue($container->has('single'));
        $this->assertTrue($container->has('ghi'));
        $this->assertNotInstanceOf(SampleBoot::class, $container->get('efg'));
        $this->assertInstanceOf(SampleBoot::class, $container->get('ghi'));

        $this->assertSame(\array_merge($classes, [
            BootloaderA::class,
            BootloaderB::class,
        ]), $bootloader->getClasses());
    }

    public function testException(): void
    {
        $this->expectException(\Spiral\Boot\Exception\ClassNotFoundException::class);

        $container = new Container();

        $bootloader = new BootloadManager($container);
        $bootloader->bootload(['Invalid']);
    }
}
