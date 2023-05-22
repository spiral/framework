<?php

declare(strict_types=1);

namespace Framework\Bootloader\Attributes;

use Spiral\Attributes\AttributeReader;
use Spiral\Attributes\ReaderInterface;
use Spiral\Bootloader\Attributes\AttributesConfig;
use Spiral\Bootloader\Attributes\Factory;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Tests\Framework\BaseTestCase;

final class AttributesWithoutAnnotationsBootloaderTest extends BaseTestCase
{
    protected function setUp(): void
    {
        $this->beforeInit(function (ConfiguratorInterface $configurator) {
            $configurator->setDefaults(AttributesConfig::CONFIG, [
                'annotations' => [
                    'support' => false,
                ],
            ]);
        });

        parent::setUp();
    }

    public function testReaderBinding(): void
    {

        $this->assertContainerBoundAsSingleton(ReaderInterface::class, AttributeReader::class);
    }
}
