<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework;

use Spiral\Auth\Middleware\AuthMiddleware;
use Spiral\Cookies\Middleware\CookiesMiddleware;
use Spiral\Csrf\Middleware\CsrfMiddleware;
use Spiral\Http\Config\HttpConfig;
use Spiral\Http\Http;
use Spiral\Session\Middleware\SessionMiddleware;

abstract class HttpTestCase extends BaseTestCase
{
    public const ENCRYPTER_KEY = 'def00000b325585e24ff3bd2d2cd273aa1d2274cb6851a9f2c514c2e2a83806f2661937f8b9cbe217e37943f5f9ccb6b5f91151606774869883e5557a941dfd879cbf5be';

    public function setHttpHandler(\Closure $handler): void
    {
        $this->getContainer()->get(Http::class)->setHandler($handler);
    }

    protected function enableMiddlewares(): void
    {
        $this->getContainer()->bind(HttpConfig::class, new HttpConfig([
            'middleware' => [
                CookiesMiddleware::class,
                SessionMiddleware::class,
                CsrfMiddleware::class,
                AuthMiddleware::class,
            ],
            'basePath' => '/',
            'headers' => []
        ]));
    }
}
