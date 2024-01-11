<?php

declare(strict_types=1);

namespace Framework\Bootloader\Attributes;

use Spiral\Attributes\AttributeReader;
use Spiral\Attributes\Psr16CachedReader;
use Spiral\Attributes\ReaderInterface;
use Spiral\Testing\Attribute\Env;
use Spiral\Tests\Framework\BaseTestCase;

final class AttributesWithoutAnnotationsBootloaderTest extends BaseTestCase
{
    public function testReaderBindingWithoutCache(): void
    {
        $this->assertContainerBoundAsSingleton(ReaderInterface::class, AttributeReader::class);
    }

    #[Env('SUPPORT_ANNOTATIONS', 'false')]
    #[Env('ATTRIBUTES_CACHE_ENABLED', 'true')]
    public function testReaderBindingWithCache(): void
    {
        $this->assertContainerBoundAsSingleton(ReaderInterface::class, Psr16CachedReader::class);
    }
}
