<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Core;

use Spiral\Core\Core;
use Spiral\Tests\BaseTest;

class CoreTest extends BaseTest
{
    public function testInstance()
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

        $this->assertFalse($this->app->hasDirectory('custom'));
        $this->app->setDirectory('custom', __DIR__);
        $this->assertTrue($this->app->hasDirectory('custom'));

        $this->assertSame($this->app->directory('custom'), directory('custom'));
        $this->assertArrayHasKey('custom', $this->app->getDirectories());
    }

    public function testTimezone()
    {
        $this->assertSame('UTC', $this->app->getTimezone()->getName());
        $this->app->setTimezone('Europe/Minsk');
        $this->assertSame('Europe/Minsk', $this->app->getTimezone()->getName());
    }

    /**
     * @expectedException \Spiral\Core\Exceptions\CoreException
     */
    public function testBadTimezone()
    {
        $this->app->setTimezone('magic');
    }
}