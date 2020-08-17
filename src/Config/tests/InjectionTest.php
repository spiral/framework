<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Config;

use Spiral\Config\Patch\Append;
use Spiral\Core\ConfigsInterface;
use Spiral\Core\InjectableConfig;

class InjectionTest extends BaseTest
{
    public function testInjection(): void
    {
        $cf = $this->getFactory();
        $this->container->bind(ConfigsInterface::class, $cf);

        $config = $this->container->get(TestConfig::class);

        $this->assertEquals(
            [
                'id'       => 'hello world',
                'autowire' => new \Spiral\Core\Container\Autowire('something')
            ],
            $config->toArray()
        );

        $this->assertSame($config, $this->container->get(TestConfig::class));
    }

    /**
     * @expectedException \Spiral\Config\Exception\ConfigDeliveredException
     */
    public function testModifyAfterInjection(): void
    {
        $cf = $this->getFactory();
        $this->container->bind(ConfigsInterface::class, $cf);

        $config = $this->container->get(TestConfig::class);

        $this->assertEquals(
            [
                'id'       => 'hello world',
                'autowire' => new \Spiral\Core\Container\Autowire('something')
            ],
            $config->toArray()
        );

        $cf->modify('test', new Append('.', null, 'value'));
    }

    public function testNonStrict(): void
    {
        $cf = $this->getFactory(null, false);
        $this->container->bind(ConfigsInterface::class, $cf);

        $config = $this->container->get(TestConfig::class);

        $this->assertEquals(
            [
                'id'       => 'hello world',
                'autowire' => new \Spiral\Core\Container\Autowire('something')
            ],
            $config->toArray()
        );

        $cf->modify('test', new Append('.', 'key', 'value'));

        $config = $this->container->get(TestConfig::class);

        $this->assertEquals(
            [
                'id'       => 'hello world',
                'autowire' => new \Spiral\Core\Container\Autowire('something'),
                'key'      => 'value'
            ],
            $config->toArray()
        );
    }
}
