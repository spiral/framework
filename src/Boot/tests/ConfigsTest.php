<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Boot;

use PHPUnit\Framework\TestCase;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Tests\Boot\Fixtures\TestConfig;
use Spiral\Tests\Boot\Fixtures\TestConfigurationCore;
use Spiral\Tests\Boot\Fixtures\TestCore;

class ConfigsTest extends TestCase
{
    public function testDirectories(): void
    {
        $core = TestCore::init([
            'root'   => __DIR__,
            'config' => __DIR__ . '/config'
        ]);

        /** @var TestConfig $config */
        $config = $core->getContainer()->get(TestConfig::class);

        $this->assertSame(['key' => 'value'], $config->toArray());
    }

    public function testDep(): void
    {
        $core = TestCore::init([
            'root'   => __DIR__,
            'config' => __DIR__ . '/config',
        ]);

        /** @var ConfiguratorInterface $config */
        $configurator = $core->getContainer()->get(ConfiguratorInterface::class);

        $this->assertSame(['test-key' => 'test value'], $configurator->getConfig('yaml'));
    }
}
