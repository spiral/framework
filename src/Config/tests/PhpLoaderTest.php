<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Config;

class PhpLoaderTest extends BaseTest
{
    public function testGetConfig(): void
    {
        $cf = $this->getFactory();

        $this->assertEquals(
            [
                'id'       => 'hello world',
                'autowire' => new \Spiral\Core\Container\Autowire('something')
            ],
            $cf->getConfig('test')
        );
    }

    /**
     * @expectedException \Spiral\Config\Exception\LoaderException
     */
    public function testEmpty(): void
    {
        $cf = $this->getFactory();
        $cf->getConfig('empty');
    }

    /**
     * @expectedException \Spiral\Config\Exception\LoaderException
     */
    public function testBroken(): void
    {
        $cf = $this->getFactory();
        $cf->getConfig('broken');
    }

    public function testScope(): void
    {
        $cf = $this->getFactory();
        $config = $cf->getConfig('scope');
        $this->assertEquals(['value' => 'value!'], $config);

        $this->container->bind(Value::class, new Value('other!'));

        $config = $cf->getConfig('scope2');
        $this->assertEquals(['value' => 'other!'], $config);

        $cf = clone $cf;

        $config = $cf->getConfig('scope');
        $this->assertEquals(['value' => 'other!'], $config);
    }
}
