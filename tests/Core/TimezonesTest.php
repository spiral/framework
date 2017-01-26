<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Core;

use Spiral\Tests\BaseTest;

class TimezonesTest extends BaseTest
{
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