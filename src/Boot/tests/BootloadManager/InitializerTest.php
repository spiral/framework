<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\BootloadManager;

use Spiral\Tests\Boot\Fixtures\AbstractBootloader;
use Spiral\Tests\Boot\Fixtures\BootloaderD;

final class InitializerTest extends InitializerTestCase
{
    public function testDontBootloadAbstractBootloader(): void
    {
        $result = \iterator_to_array($this->initializer->init([AbstractBootloader::class, BootloaderD::class]));

        self::assertCount(1, $result);
        self::assertIsArray($result[BootloaderD::class]);
    }
}
