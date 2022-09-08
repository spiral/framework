<?php

declare(strict_types=1);

namespace Spiral\Tests\Cookies;

use PHPUnit\Framework\TestCase;
use Spiral\Cookies\Config\CookiesConfig;
use Nyholm\Psr7\Uri;

class ConfigTest extends TestCase
{
    public function testCookies(): void
    {
        $c = new CookiesConfig([
            'domain'   => '.%s',
            'method'   => CookiesConfig::COOKIE_ENCRYPT,
            'excluded' => ['PHPSESSID', 'csrf-token']

        ]);

        $this->assertSame(CookiesConfig::COOKIE_ENCRYPT, $c->getProtectionMethod());
        $this->assertSame(['PHPSESSID', 'csrf-token'], $c->getExcludedCookies());
    }

    public function testCookieDomain(): void
    {
        $c = new CookiesConfig([
            'domain' => '.%s',
        ]);

        $this->assertSame('.domain.com', $c->resolveDomain(new Uri('http://domain.com/')));
        $this->assertSame('.domain.com', $c->resolveDomain(new Uri('https://domain.com/')));
        $this->assertSame('.domain.com', $c->resolveDomain(new Uri('https://domain.com:9090/')));
        $this->assertSame(null, $c->resolveDomain(new Uri('/')));
        $this->assertSame('localhost', $c->resolveDomain(new Uri('localhost:9090/')));

        $this->assertSame('192.169.1.10', $c->resolveDomain(new Uri('http://192.169.1.10:8080/')));

        $c = new CookiesConfig([
            'domain' => '.doo.com',
        ]);

        $this->assertSame('.doo.com', $c->resolveDomain(new Uri('http://domain.com/')));
    }
}
