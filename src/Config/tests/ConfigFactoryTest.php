<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Config;

class ConfigFactoryTest extends BaseTest
{
    public function testGetConfig(): void
    {
        $cf = $this->getFactory();
        $config = $cf->getConfig('test');

        $this->assertEquals(
            [
                'id'       => 'hello world',
                'autowire' => new \Spiral\Core\Container\Autowire('something')
            ],
            $config
        );

        $this->assertSame($config, $cf->getConfig('test'));
    }

    public function testExists(): void
    {
        $cf = $this->getFactory();
        $this->assertTrue($cf->exists('test'));
        $this->assertFalse($cf->exists('magic'));

        $cf->setDefaults('magic', ['key' => 'value']);

        $this->assertTrue($cf->exists('magic'));
    }

    /**
     * @expectedException \Spiral\Config\Exception\LoaderException
     */
    public function testConfigError(): void
    {
        $cf = $this->getFactory();
        $cf->getConfig('other');
    }
}
