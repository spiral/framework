<?php

declare(strict_types=1);

namespace Spiral\Tests\Cookies;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Cookies\Cookie;

class CookieTest extends TestCase
{
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

    public function testAccess(): void
    {
        $cookie = new Cookie(
            'cookie',
            'value',
            100,
            '/',
            '.domain.com',
            true,
            true,
        );

        self::assertSame('cookie', $cookie->getName());
        self::assertSame('value', $cookie->getValue());
        self::assertSame(\time() + 100, $cookie->getExpires());
        self::assertSame('.domain.com', $cookie->getDomain());
        self::assertTrue($cookie->isSecure());
        self::assertTrue($cookie->isHttpOnly());
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
            true,
        );

        self::assertSame('cookie', $cookie->getName());
        self::assertSame('value', $cookie->getValue());
        self::assertSame(\time() + 100, $cookie->getExpires());
        self::assertSame('.domain.com', $cookie->getDomain());
        self::assertTrue($cookie->isSecure());
        self::assertTrue($cookie->isHttpOnly());
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
            true,
        );

        self::assertNull($cookie->getExpires());
        self::assertNull($cookie->getPath());
        self::assertNull($cookie->getDomain());
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
            true,
        );

        $cookie1 = $cookie->withValue('new-value');

        self::assertNotSame($cookie, $cookie1);
        self::assertSame('value', $cookie->getValue());
        self::assertSame('new-value', $cookie1->getValue());
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
            true,
        );

        self::assertSame($cookie->createHeader(), (string) $cookie);
        self::assertStringContainsString('cookie=value;', $cookie->createHeader());

        self::assertStringContainsString('Max-Age=100; Path=/; Domain=.domain.com; Secure; HttpOnly', $cookie->createHeader());
    }

    #[DataProvider('sameSiteProvider')]
    public function testSameSite(?string $expected, bool $secure, ?string $sameSite): void
    {
        $cookie = new Cookie('', '', 0, '', '', $secure, false, $sameSite);
        self::assertSame($expected, $cookie->getSameSite());

        if ($expected === null) {
            self::assertStringNotContainsString('SameSite=', $cookie->createHeader());
        } else {
            self::assertStringContainsString("SameSite=$expected", $cookie->createHeader());
        }
    }
}
