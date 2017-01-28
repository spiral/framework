<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Views;

use Spiral\Tests\BaseTest;

class ManagerTest extends BaseTest
{
    /**
     * @expectedException \Spiral\Views\Exceptions\ViewsException
     */
    public function testError()
    {
        $this->views->engine('wrong');
    }
}