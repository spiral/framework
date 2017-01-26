<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Core;

use Spiral\Tests\BaseTest;

class DirectoriesTest extends BaseTest
{
    /**
     * @expectedException \Spiral\Core\Exceptions\DirectoryException
     */
    public function testGetInvalid()
    {
        $this->app->directory('abc');
    }

    public function testGetDirectoryShortcut()
    {
        $this->assertSame($this->app->directory('application'), directory('application'));
    }

    public function testCreateDirectory()
    {
        $this->assertFalse($this->app->hasDirectory('custom'));
        $this->app->setDirectory('custom', __DIR__);
        $this->assertTrue($this->app->hasDirectory('custom'));
    }

    public function testGetAll()
    {
        $this->app->setDirectory('custom', __DIR__);
        $this->assertSame($this->app->directory('custom'), directory('custom'));
        $this->assertArrayHasKey('custom', $this->app->getDirectories());
    }
}