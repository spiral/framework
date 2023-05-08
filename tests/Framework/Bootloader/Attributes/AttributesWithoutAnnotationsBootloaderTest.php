<?php

declare(strict_types=1);

namespace Framework\Bootloader\Attributes;

use Spiral\Attributes\AttributeReader;
use Spiral\Attributes\ReaderInterface;
use Spiral\Bootloader\Attributes\Factory;
use Spiral\Tests\Framework\BaseTestCase;

final class AttributesWithoutAnnotationsBootloaderTest extends BaseTestCase
{
    public const ENV = [
        'SUPPORT_ANNOTATIONS' => false,
    ];

    public function testReaderBinding(): void
    {
        $this->assertContainerBoundAsSingleton(ReaderInterface::class, AttributeReader::class);
    }
}
