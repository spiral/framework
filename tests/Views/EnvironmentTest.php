<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Views;

use Spiral\Tests\BaseTest;

class EnvironmentTest extends BaseTest
{
    public function testImmutable()
    {
        $environment = $this->views->getEnvironment();
        $newEnvironment = $environment->withDependency('test', function () {
            return 1;
        });

        $this->assertSame(1, $newEnvironment->getValue('test'));
        $this->assertNotSame($environment->getID(), $newEnvironment->getID());
    }

    /**
     * @expectedException \Spiral\Views\Exceptions\EnvironmentException
     */
    public function testMissingDependency()
    {
        $environment = $this->views->getEnvironment();
        $environment->getValue('test');
    }
}