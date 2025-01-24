<?php

declare(strict_types=1);

namespace Spiral\Tests\Http;

use PHPUnit\Framework\TestCase;
use Spiral\Http\Config\HttpConfig;

class ConfigTest extends TestCase
{
    public function testBasePath(): void
    {
        $c = new HttpConfig([
            'basePath' => '/',
        ]);

        self::assertSame('/', $c->getBasePath());
    }

    public function testBaseHeaders(): void
    {
        $c = new HttpConfig([
            'headers' => [
                'key' => 'value',
            ],
        ]);

        self::assertSame(['key' => 'value'], $c->getBaseHeaders());
    }

    public function testBaseMiddleware(): void
    {
        $c = new HttpConfig([
            'middleware' => [TestMiddleware::class],
        ]);

        self::assertSame([TestMiddleware::class], $c->getMiddleware());
    }
}
