<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Http;

use Spiral\Http\Cookies\Cookie;

class CookieTest extends \PHPUnit_Framework_TestCase
{
    public function testAccess()
    {
        $cookie = new Cookie(
            'cookie',
            'value',
            100,
            '/',
            '.domain.com',
            true,
            true
        );

        $this->assertSame('cookie', $cookie->getName());
        $this->assertSame('value', $cookie->getValue());
        $this->assertSame(time() + 100, $cookie->getExpires());
        $this->assertSame('.domain.com', $cookie->getDomain());
        $this->assertTrue($cookie->isSecure());
        $this->assertTrue($cookie->isHttpOnly());
    }

    public function testFallbackValues()
    {
        $cookie = new Cookie(
            'cookie',
            'value',
            null,
            null,
            null,
            true,
            true
        );

        $this->assertNull($cookie->getExpires());
        $this->assertNull($cookie->getPath());
        $this->assertNull($cookie->getDomain());
    }

    public function testWithValue()
    {
        $cookie = new Cookie(
            'cookie',
            'value',
            null,
            null,
            null,
            true,
            true
        );

        $cookie1 = $cookie->withValue('new-value');

        $this->assertNotSame($cookie, $cookie1);
        $this->assertSame('value', $cookie->getValue());
        $this->assertSame('new-value', $cookie1->getValue());
    }

    public function testPack()
    {
        $cookie = new Cookie(
            'cookie',
            'value',
            100,
            '/',
            '.domain.com',
            true,
            true
        );

        $this->assertSame($cookie->createHeader(), (string)$cookie);
        $this->assertContains(
            'cookie=value;',
            $cookie->createHeader()
        );

        $this->assertContains(
            'Max-Age=100; Path=/; Domain=.domain.com; Secure; HttpOnly',
            $cookie->createHeader()
        );
    }
}