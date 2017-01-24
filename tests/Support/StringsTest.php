<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Support;

use Spiral\Support\Strings;
use Spiral\Tests\BaseTest;

class StringsTest extends BaseTest
{
    public function testRandom()
    {
        $this->assertSame(32, strlen(Strings::random(32)));
        $this->assertSame(64, strlen(Strings::random(64)));
    }

    public function testEscape()
    {

    }
}