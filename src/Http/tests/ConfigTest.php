<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Http;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Http\Config\HttpConfig;

class ConfigTest extends TestCase
{
    public function testBasePath(): void
    {
        $c = new HttpConfig([
            'basePath' => '/'
        ]);

        $this->assertSame('/', $c->getBasePath());
    }

    public function testBaseHeaders(): void
    {
        $c = new HttpConfig([
            'headers' => [
                'key' => 'value'
            ]
        ]);

        $this->assertSame(['key' => 'value'], $c->getBaseHeaders());
    }

    public function testBaseMiddleware(): void
    {
        $c = new HttpConfig([
            'middleware' => [TestMiddleware::class]
        ]);

        $this->assertSame([TestMiddleware::class], $c->getMiddleware());
    }

    public function testBaseMiddlewareFallback(): void
    {
        $c = new HttpConfig([
            'middlewares' => [TestMiddleware::class]
        ]);

        $this->assertSame([TestMiddleware::class], $c->getMiddleware());
    }
}
