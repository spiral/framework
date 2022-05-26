<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Bootloader\Http;

use Spiral\Bootloader\Http\HttpBootloader;
use Spiral\Http\Config\HttpConfig;
use Spiral\Tests\Framework\BaseTest;

final class HttpBootloaderTest extends BaseTest
{
    public function testDefaultInputBags(): void
    {
        $app = $this->makeApp();

        $this->assertSame([], $app->get(HttpConfig::class)->getInputBags());
    }

    public function testAddInputBag(): void
    {
        $app = $this->makeApp();

        /** @var HttpBootloader $bootloader */
        $bootloader = $app->get(HttpBootloader::class);
        $bootloader->addInputBag('test', ['class' => 'foo', 'source' => 'bar']);

        $this->assertSame([
            'test' => ['class' => 'foo', 'source' => 'bar']
        ], $app->get(HttpConfig::class)->getInputBags());
    }
}
