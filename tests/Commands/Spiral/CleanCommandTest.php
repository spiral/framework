<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Commands\Spiral;

use Spiral\Tests\BaseTest;

class CleanCommandTest extends BaseTest
{
    public function testClean()
    {
        $this->assertEmpty($this->files->getFiles(directory('cache')));
        $this->files->write(directory('cache') . 'abc', 'data');
        $this->console->run('app:clean');
        $this->assertEmpty($this->files->getFiles(directory('cache')));
    }
}