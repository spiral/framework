<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Commands\Spiral;

use Spiral\Core\Bootloaders\SpiralBindings;
use Spiral\Tests\BaseTest;

class BootloadsCommandTest extends BaseTest
{
    public function testBootloads()
    {
        $output = $this->console->run('app:bootloads');

        //todo: update when more bootloaders will be added to application
        $this->assertContains(SpiralBindings::class, $output->getOutput()->fetch());
    }
}