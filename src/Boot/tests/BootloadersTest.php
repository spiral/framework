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
use Spiral\Core\Exception\Container\NotFoundException;
use Spiral\Tests\Boot\Fixtures\SampleBoot;
use Spiral\Tests\Boot\Fixtures\SampleClass;
use Spiral\Core\Container;

class BootloadersTest extends TestCase
{
    public function testSchemaLoading(): void
    {
        $container = new Container();

        $bootloader = new BootloadManager($container);
        $bootloader->bootload([SampleClass::class, SampleBoot::class]);

        $this->assertTrue($container->has('abc'));
        $this->assertTrue($container->hasInstance('cde'));
        $this->assertTrue($container->has('single'));

        $this->assertSame([SampleClass::class, SampleBoot::class], $bootloader->getClasses());
    }

    public function testException(): void
    {
        $this->expectException(NotFoundException::class);

        $container = new Container();

        $bootloader = new BootloadManager($container);
        $bootloader->bootload(['Invalid']);
    }
}
