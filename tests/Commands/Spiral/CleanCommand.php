<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Commands\Spiral;

use Spiral\Tests\BaseTest;

class CleanCommand extends BaseTest
{
    public function testClean()
    {
        $this->assertNotEmpty($this->files->getFiles(directory('runtime')));
        $this->console->run('app:clean');
        $this->assertEmpty($this->files->getFiles(directory('runtime')));
    }
}