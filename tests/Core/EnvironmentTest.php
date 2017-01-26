<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Core;

use Spiral\Core\EnvironmentInterface;
use Spiral\Tests\BaseTest;

class EnvironmentTest extends BaseTest
{
    public function testInstance()
    {
        $this->assertInstanceOf(EnvironmentInterface::class, $this->app->getEnvironment());
    }
}