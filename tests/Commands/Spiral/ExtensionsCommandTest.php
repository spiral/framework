<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Commands\Spiral;

use Spiral\Tests\BaseTest;

class ExtensionsCommandTest extends BaseTest
{
    public function testBootloads()
    {
        $output = $this->console->run('app:extensions')->getOutput()->fetch();

        foreach (get_loaded_extensions() as $extension) {
            $this->assertContains($extension, $output);
        }
    }
}