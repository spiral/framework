<?php

declare(strict_types=1);

namespace Spiral\Tests\Csrf;

use PHPUnit\Framework\TestCase;
use Spiral\Csrf\Config\CsrfConfig;

final class ConfigTest extends TestCase
{
    public function testCsrf(): void
    {
        $c = new CsrfConfig([
            'cookie'   => 'csrf-token',
            'length'   => 16,
            'lifetime' => 86400,
            'sameSite' => 'Lax',
            'path'     => '/admin',
        ]);

        self::assertSame('csrf-token', $c->getCookie());
        self::assertSame(16, $c->getTokenLength());
        self::assertSame(86400, $c->getCookieLifetime());
        self::assertFalse($c->isCookieSecure());
        self::assertSame('Lax', $c->getSameSite());
        self::assertSame('/admin', $c->getCookiePath());

        $c = new CsrfConfig([
            'cookie' => 'csrf-token',
            'length' => 16,
            'secure' => true,
        ]);

        self::assertNull($c->getCookieLifetime());
        self::assertTrue($c->isCookieSecure());
        self::assertNull($c->getSameSite());
        self::assertSame('/', $c->getCookiePath());
    }

    public function testEmptyCookiePathFallsBackToRoot(): void
    {
        $c = new CsrfConfig([
            'cookie' => 'csrf-token',
            'length' => 16,
            'path'   => '',
        ]);

        // An empty path would make Cookie::createHeader() drop the Path= attribute.
        self::assertSame('/', $c->getCookiePath());
    }
}
