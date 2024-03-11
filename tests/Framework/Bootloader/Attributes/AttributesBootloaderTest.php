<?php

declare(strict_types=1);

namespace Framework\Bootloader\Attributes;

use Spiral\Attributes\Composite\SelectiveReader;
use Spiral\Attributes\Internal\Instantiator\Facade;
use Spiral\Attributes\Internal\Instantiator\InstantiatorInterface;
use Spiral\Attributes\ReaderInterface;
use Spiral\Bootloader\Attributes\AttributesConfig;
use Spiral\Tests\Framework\BaseTestCase;

final class AttributesBootloaderTest extends BaseTestCase
{
    public function testReaderBinding(): void
    {
        $this->assertContainerBoundAsSingleton(ReaderInterface::class, SelectiveReader::class);
    }

    public function testInstantiatorBinding(): void
    {
        $this->assertContainerBoundAsSingleton(InstantiatorInterface::class, Facade::class);
    }

    public function testIsCacheEnabledShouldBeFalse(): void
    {
        $this->assertFalse($this->getConfig(AttributesConfig::CONFIG)['cache']['enabled']);
    }

    public function testGetStorageShouldBeNull(): void
    {
        $this->assertNull($this->getConfig(AttributesConfig::CONFIG)['cache']['storage']);
    }
}
