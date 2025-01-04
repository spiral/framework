<?php

declare(strict_types=1);

namespace Framework\Bootloader\Attributes;

use PHPUnit\Framework\TestCase;
use Spiral\Bootloader\Attributes\AttributesConfig;

final class AttributesConfigTest extends TestCase
{
    public function testIsAnnotationsReaderEnabled(): void
    {
        self::assertTrue((new AttributesConfig())->isAnnotationsReaderEnabled());
        self::assertFalse((new AttributesConfig(['annotations' => ['support' => false]]))->isAnnotationsReaderEnabled());
    }

    public function testIsCacheEnabled(): void
    {
        self::assertFalse((new AttributesConfig())->isCacheEnabled());
        self::assertTrue((new AttributesConfig(['cache' => ['enabled' => true]]))->isCacheEnabled());
    }

    public function testGetCacheStorage(): void
    {
        self::assertNull((new AttributesConfig())->getCacheStorage());
        self::assertSame('test', (new AttributesConfig(['cache' => ['storage' => 'test']]))->getCacheStorage());
    }
}
