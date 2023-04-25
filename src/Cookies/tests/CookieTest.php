<?php

declare(strict_types=1);

namespace Spiral\Tests\Cookies;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Cookies\Cookie;

class CookieTest extends TestCase
{
    public function testAccess(): void
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

    public function testCreateStaticAccess(): void
    {
        $cookie = Cookie::create(
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

    public function testFallbackValues(): void
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

    public function testWithValue(): void
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

    public function testPack(): void
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
        $this->assertStringContainsString(
            'cookie=value;',
            $cookie->createHeader()
        );

        $this->assertStringContainsString(
            'Max-Age=100; Path=/; Domain=.domain.com; Secure; HttpOnly',
            $cookie->createHeader()
        );
    }

    #[DataProvider('sameSiteProvider')]
    public function testSameSite(?string $expected, bool $secure, ?string $sameSite): void
    {
        $cookie = new Cookie('', '', 0, '', '', $secure, false, $sameSite);
        $this->assertSame($expected, $cookie->getSameSite());

        if ($expected === null) {
            $this->assertStringNotContainsString('SameSite=', $cookie->createHeader());
        } else {
            $this->assertStringContainsString("SameSite=$expected", $cookie->createHeader());
        }
    }

    public static function sameSiteProvider(): \Traversable
    {
        yield [null, true, null];
        yield [null, false, null];
        yield [null, true, 'weird'];
        yield [null, false, 'weird'];
        yield ['Lax', true, 'lax'];
        yield ['Lax', false, 'lax'];
        yield ['Strict', true, 'strict'];
        yield ['Strict', false, 'strict'];
        yield ['None', true, 'none'];
        yield ['Lax', false, 'none'];
    }
}
