<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Console;

use Spiral\Console\ConsoleDispatcher;
use Spiral\Tests\BaseTest;

class ConfigureTest extends BaseTest
{
    public function testConfigure()
    {
        $this->assertSame(
            ConsoleDispatcher::CODE_COMPLETED,
            $this->app->console->command('spiral:configure')->getCode()
        );
    }
}