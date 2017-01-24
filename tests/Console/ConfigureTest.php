<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Console;

use Spiral\Tests\BaseTest;

class ConfigureTest extends BaseTest
{
    public function testConfigure()
    {
        $this->assertSame(0, $this->app->console->run('configure')->getCode());
    }
}