<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\BootloadManager;

use PHPUnit\Framework\TestCase;
use Spiral\Boot\BootloadManager\Initializer;
use Spiral\Core\Container;
use Spiral\Tests\Boot\Fixtures\AbstractBootloader;
use Spiral\Tests\Boot\Fixtures\BootloaderD;

final class InitializerTest extends TestCase
{
    public function testDontBootloadAbstractBootloader(): void
    {
        $container = new Container();

        $initializer = new Initializer($container, $container);

        $result = \iterator_to_array($initializer->init([AbstractBootloader::class, BootloaderD::class]));

        $this->assertCount(1, $result);
        $this->assertIsArray($result[BootloaderD::class]);
    }
}
