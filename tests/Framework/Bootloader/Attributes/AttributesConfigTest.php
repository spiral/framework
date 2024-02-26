<?php

declare(strict_types=1);

namespace Framework\Bootloader\Attributes;

use PHPUnit\Framework\TestCase;
use Spiral\Bootloader\Attributes\AttributesConfig;

final class AttributesConfigTest extends TestCase
{
    public function testIsAnnotationsReaderEnabled(): void
    {
        $this->assertTrue((new AttributesConfig())->isAnnotationsReaderEnabled());
        $this->assertFalse(
            (new AttributesConfig(['annotations' => ['support' => false]]))->isAnnotationsReaderEnabled()
        );
    }

    public function testIsCacheEnabled(): void
    {
        $this->assertFalse((new AttributesConfig())->isCacheEnabled());
        $this->assertTrue((new AttributesConfig(['cache' => ['enabled' => true]]))->isCacheEnabled());
    }

    public function testGetCacheStorage(): void
    {
        $this->assertNull((new AttributesConfig())->getCacheStorage());
        $this->assertSame(
            'test',
            (new AttributesConfig(['cache' => ['storage' => 'test']]))->getCacheStorage()
        );
    }
}
