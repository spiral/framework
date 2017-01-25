<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Console;

use Spiral\Console\NullLocator;

class NullLocatorTest extends \PHPUnit_Framework_TestCase
{
    public function testNull()
    {
        $locator = new NullLocator();
        $this->assertSame([], $locator->locateCommands());
    }
}