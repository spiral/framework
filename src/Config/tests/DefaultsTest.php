<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Config;

use Spiral\Core\Container\Autowire;
use Spiral\Core\Exception\ConfiguratorException;

class DefaultsTest extends BaseTest
{
    public function testGetNonExistedByDefaultConfig(): void
    {
        $cf = $this->getFactory();
        $cf->setDefaults('magic', ['key' => 'value']);

        $config = $cf->getConfig('magic');

        $this->assertEquals(
            ['key' => 'value'],
            $config
        );

        $this->assertSame($config, $cf->getConfig('magic'));
    }

    public function testDefaultsTwice(): void
    {
        $this->expectException(ConfiguratorException::class);

        $cf = $this->getFactory();
        $cf->setDefaults('magic', ['key' => 'value']);
        $cf->setDefaults('magic', ['key' => 'value']);
    }

    public function testDefaultToAlreadyLoaded(): void
    {
        $this->expectException(ConfiguratorException::class);

        $cf = $this->getFactory();

        $cf->getConfig('test');
        $cf->setDefaults('test', ['key' => 'value']);
    }

    public function testOverwrite(): void
    {
        $cf = $this->getFactory();

        $cf->setDefaults('test', [
            'key' => 'value',
        ]);

        $config = $cf->getConfig('test');

        $this->assertEquals(
            [
                'key'      => 'value',
                'id'       => 'hello world',
                'autowire' => new Autowire('something'),
            ],
            $config
        );

        $this->assertSame($config, $cf->getConfig('test'));
    }
}
