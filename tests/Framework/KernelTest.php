<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework;

use Spiral\Config\ConfiguratorInterface;

class KernelTest extends BaseTest
{
    public function testBypassEnvironmentToConfig()
    {
        $configs = $this->makeApp([
            'TEST_VALUE' => 'HELLO WORLD'
        ])->get(ConfiguratorInterface::class);

        $this->assertSame([
            'key' => 'HELLO WORLD'
        ], $configs->getConfig('test'));
    }
}