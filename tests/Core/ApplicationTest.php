<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Core;

use Spiral\Core\Core;
use Spiral\Tests\BaseTest;

class ApplicationTest extends BaseTest
{
    public function testCore()
    {
        $this->assertInstanceOf(Core::class, $this->app);
    }

    public function testShortcut()
    {
        $this->assertSame($this->app->container, spiral('container'));
    }

    public function testDirectories()
    {
        $this->assertSame($this->app->directory('application'), directory('application'));
    }
}