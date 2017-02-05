<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Commands\Spiral;

use Spiral\Core\DotenvEnvironment;
use Spiral\Core\NullMemory;
use Spiral\Tests\BaseTest;

class KeyCommandTest extends BaseTest
{
    public function testConfigureAndKey()
    {
        $environment = new DotenvEnvironment(
            directory('root') . '.env',
            new NullMemory()
        );

        //This is very complex and MUST not fail!
        $this->assertSame(0, $this->console->run('app:key')->getCode());

        clearstatcache();
        $newEnvironment = new DotenvEnvironment(
            directory('root') . '.env',
            new NullMemory()
        );

        $this->assertNotSame(
            $environment->get('SPIRAL_KEY'),
            $newEnvironment->get('SPIRAL_KEY')
        );
    }
}