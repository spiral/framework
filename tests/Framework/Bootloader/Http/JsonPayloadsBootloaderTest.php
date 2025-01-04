<?php

declare(strict_types=1);

namespace Framework\Bootloader\Http;

use Spiral\Bootloader\Http\JsonPayloadConfig;
use Spiral\Bootloader\Http\JsonPayloadsBootloader;
use Spiral\Config\ConfigManager;
use Spiral\Config\LoaderInterface;
use Spiral\Tests\Framework\BaseTestCase;

final class JsonPayloadsBootloaderTest extends BaseTestCase
{
    public function testConfig(): void
    {
        $this->assertConfigMatches(
            JsonPayloadConfig::CONFIG,
            [
                'contentTypes' => [
                    'application/json',
                    'application/vnd.api+json'
                ],
            ]
        );
    }

    public function testAddContentType(): void
    {
        $configs = new ConfigManager($this->createMock(LoaderInterface::class));
        $configs->setDefaults(JsonPayloadConfig::CONFIG, ['contentTypes' => []]);

        $bootloader = new JsonPayloadsBootloader($configs);
        $bootloader->addContentType('foo');

        self::assertSame(['foo'], $configs->getConfig(JsonPayloadConfig::CONFIG)['contentTypes']);
    }
}
