<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Core;

use Spiral\Core\ConfiguratorInterface;
use Spiral\Http\Configs\HttpConfig;
use Spiral\Tests\BaseTest;

class ConfigsTest extends BaseTest
{
    public function testFactory()
    {
        /** @var \Spiral\Core\ConfigFactory $factory */
        $factory = $this->container->get(ConfiguratorInterface::class);

        $config = $factory->createInjection(new \ReflectionClass(HttpConfig::class));
        $this->assertInstanceOf(HttpConfig::class, $config);
    }

    public function testInstanceCaching()
    {
        /** @var \Spiral\Core\ConfigFactory $factory */
        $factory = $this->container->get(ConfiguratorInterface::class);

        $config = $factory->createInjection(new \ReflectionClass(HttpConfig::class));
        $this->assertSame(
            $config,
            $factory->createInjection(new \ReflectionClass(HttpConfig::class))
        );

        $factory->flushCache();

        $this->assertNotSame(
            $config,
            $factory->createInjection(new \ReflectionClass(HttpConfig::class))
        );
    }

    /**
     * @expectedException \Spiral\Core\Exceptions\ConfiguratorException
     */
    public function testUndefinedConfig()
    {
        /** @var \Spiral\Core\ConfigFactory $factory */
        $factory = $this->container->get(ConfiguratorInterface::class);

        $factory->getConfig('missing');
    }


    /**
     * @expectedException \Spiral\Core\Exceptions\ConfiguratorException
     */
    public function testBrokenConfig()
    {
        /** @var \Spiral\Core\ConfigFactory $factory */
        $factory = $this->container->get(ConfiguratorInterface::class);

        $factory->getConfig('broken');
    }
}