<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Bootloader\Http;

use Spiral\Http\Config\HttpConfig;
use Spiral\Tests\Framework\BaseTest;

final class HttpBootloaderTest extends BaseTest
{
    public function testAddInputBag(): void
    {
        $app = $this->makeApp();

        $this->assertSame([
            'test' => ['class' => 'foo', 'source' => 'bar']
        ], $app->get(HttpConfig::class)->getInputBags());
    }
}
