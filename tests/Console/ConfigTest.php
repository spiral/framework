<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Console;

use Spiral\Console\Configs\ConsoleConfig;
use Spiral\Tests\BaseTest;

class ConfigTest extends BaseTest
{
    public function testLegacyConfigFormat()
    {
        $config = new ConsoleConfig();
        $this->assertTrue($config->locateCommands());

        $this->assertSame([], $config->userCommands());
    }
}