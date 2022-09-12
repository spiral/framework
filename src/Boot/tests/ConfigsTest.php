<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot;

use Spiral\Config\ConfiguratorInterface;
use Spiral\Tests\Boot\Fixtures\TestConfig;
use Spiral\Tests\Boot\Fixtures\TestCore;

class ConfigsTest extends TestCase
{
    public function testDirectories(): void
    {
        $core = TestCore::create([
            'root'   => __DIR__,
            'config' => __DIR__ . '/config'
        ])->run();

        /** @var TestConfig $config */
        $config = $core->getContainer()->get(TestConfig::class);

        $this->assertSame(['key' => 'value'], $config->toArray());
    }

    public function testCustomConfigLoader(): void
    {
        $core = TestCore::create([
            'root'   => __DIR__,
            'config' => __DIR__ . '/config',
        ])->run();

        /** @var ConfiguratorInterface $config */
        $configurator = $core->getContainer()->get(ConfiguratorInterface::class);

        $this->assertSame(['test-key' => 'test value'], $configurator->getConfig('yaml'));
    }
}
