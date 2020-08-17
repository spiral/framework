<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Config;

class JsonLoaderTest extends BaseTest
{
    public function testGetConfig(): void
    {
        $cf = $this->getFactory();

        $this->assertEquals(['name' => 'value'], $cf->getConfig('json'));
    }

    /**
     * @expectedException \Spiral\Config\Exception\LoaderException
     */
    public function testEmpty(): void
    {
        $cf = $this->getFactory();
        $cf->getConfig('empty-json');
    }

    /**
     * @expectedException \Spiral\Config\Exception\LoaderException
     */
    public function testBroken(): void
    {
        $cf = $this->getFactory();
        $cf->getConfig('broken-json');
    }
}
