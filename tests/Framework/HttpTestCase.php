<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework;

use Spiral\Auth\Middleware\AuthMiddleware;
use Spiral\Cookies\Middleware\CookiesMiddleware;
use Spiral\Core\Container;
use Spiral\Core\Scope;
use Spiral\Csrf\Middleware\CsrfMiddleware;
use Spiral\Framework\ScopeName;
use Spiral\Http\Config\HttpConfig;
use Spiral\Http\Http;
use Spiral\Session\Middleware\SessionMiddleware;
use Spiral\Testing\Http\FakeHttp;
use Spiral\Testing\Http\TestResponse;

abstract class HttpTestCase extends BaseTestCase
{
    public const ENCRYPTER_KEY = 'def00000b325585e24ff3bd2d2cd273aa1d2274cb6851a9f2c514c2e2a83806f2661937f8b9cbe217e37943f5f9ccb6b5f91151606774869883e5557a941dfd879cbf5be';

    public function get(
        string $uri,
        array $query = [],
        array $headers = [],
        array $cookies = [],
        mixed $handler = null
    ): mixed {
        return $this->getContainer()->runScope(
            new Scope(ScopeName::Http),
            function (Container $container, Http $http) use ($uri, $query, $headers, $cookies, $handler): TestResponse {
                if ($handler !== null) {
                    $http->setHandler($handler);
                }

                $fakeHttp = new FakeHttp(
                    $container,
                    $this->getFileFactory(),
                    fn (\Closure $closure, array $bindings): mixed => $this->runScoped($closure, $bindings)
                );

                return $fakeHttp->get($uri, $query, $headers, $cookies);
            }
        );
    }

    public function post(
        string $uri,
        mixed $data = [],
        array $headers = [],
        array $cookies = [],
        array $files = [],
        mixed $handler = null
    ): mixed {
        return $this->getContainer()->runScope(
            new Scope(ScopeName::Http),
            function (Container $container, Http $http) use ($uri, $data, $headers, $cookies, $files, $handler) {
                if ($handler !== null) {
                    $http->setHandler($handler);
                }

                $fakeHttp = new FakeHttp(
                    $container,
                    $this->getFileFactory(),
                    fn (\Closure $closure, array $bindings): mixed => $this->runScoped($closure, $bindings)
                );

                return $fakeHttp->post($uri, $data, $headers, $cookies, $files);
            }
        );
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
