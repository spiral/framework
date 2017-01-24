<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Http;

use Spiral\Http\Uri;

class UriTest extends \PHPUnit_Framework_TestCase
{
    public function testJsonSerialize()
    {
        $uri = new Uri('http://google.com/hack-me?what#yes');
        $this->assertSame($uri->__toString(), $uri->jsonSerialize());
    }
}