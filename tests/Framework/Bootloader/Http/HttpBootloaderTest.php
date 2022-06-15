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
        $this->assertSame([], $this->getContainer()->get(HttpConfig::class)->getInputBags());
    }

    public function testAddInputBag(): void
    {
        /** @var HttpBootloader $bootloader */
        $bootloader = $this->getContainer()->get(HttpBootloader::class);

        $bootloader->addInputBag('test', ['class' => 'foo', 'source' => 'bar']);

        $this->assertSame([
            'test' => ['class' => 'foo', 'source' => 'bar']
        ], $this->getContainer()->get(HttpConfig::class)->getInputBags());
    }
}
