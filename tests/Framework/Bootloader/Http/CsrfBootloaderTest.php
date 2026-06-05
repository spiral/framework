<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Bootloader\Http;

use Spiral\Csrf\Config\CsrfConfig;
use Spiral\Tests\Framework\BaseTestCase;

final class CsrfBootloaderTest extends BaseTestCase
{
    public function testConfig(): void
    {
        $this->assertConfigMatches(CsrfConfig::CONFIG, [
            'cookie'   => 'csrf-token',
            'length'   => 16,
            'lifetime' => 86400,
            'secure'   => true,
            'sameSite' => null,
            'path'     => '/',
        ]);
    }

    public function testCookiePathDefault(): void
    {
        $config = $this->getContainer()->get(CsrfConfig::class);

        self::assertSame('/', $config->getCookiePath());
    }
}
