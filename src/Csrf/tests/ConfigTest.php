<?php

declare(strict_types=1);

namespace Spiral\Tests\Csrf;

use PHPUnit\Framework\TestCase;
use Spiral\Csrf\Config\CsrfConfig;

class ConfigTest extends TestCase
{
    public function testCsrf(): void
    {
        $c = new CsrfConfig([
            'cookie'   => 'csrf-token',
            'length'   => 16,
            'lifetime' => 86400,
            'sameSite' => 'Lax'
        ]);

        $this->assertSame('csrf-token', $c->getCookie());
        $this->assertSame(16, $c->getTokenLength());
        $this->assertSame(86400, $c->getCookieLifetime());
        $this->assertFalse($c->isCookieSecure());
        $this->assertSame('Lax', $c->getSameSite());

        $c = new CsrfConfig([
            'cookie' => 'csrf-token',
            'length' => 16,
            'secure' => true
        ]);

        $this->assertNull($c->getCookieLifetime());
        $this->assertTrue($c->isCookieSecure());
        $this->assertNull($c->getSameSite());
    }
}
