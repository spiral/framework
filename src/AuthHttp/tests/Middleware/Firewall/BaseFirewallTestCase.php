<?php

declare(strict_types=1);

namespace Spiral\Tests\Auth\Middleware\Firewall;

use Spiral\Auth\HttpTransportInterface;
use Spiral\Auth\Middleware\AuthMiddleware;
use Spiral\Auth\Middleware\Firewall\AbstractFirewall;
use Spiral\Auth\TransportRegistry;
use Spiral\Http\Config\HttpConfig;
use Spiral\Http\Http;
use Spiral\Http\Pipeline;
use Spiral\Tests\Auth\BaseTestCase;
use Spiral\Tests\Auth\Diactoros\ResponseFactory;
use Spiral\Tests\Auth\Stub\TestAuthHttpProvider;
use Spiral\Tests\Auth\Stub\TestAuthHttpStorage;

abstract class BaseFirewallTestCase extends BaseTestCase
{
    public static function successTokensDataProvider(): \Traversable
    {
        // ok
        yield ['ok'];
    }

    public static function failTokensDataProvider(): \Traversable
    {
        // Actor not found
        yield ['no-actor'];

        // Bad token
        yield ['bad'];
    }

    protected function getCore(AbstractFirewall $firewall, HttpTransportInterface $transport): Http
    {
        $config = new HttpConfig([
            'basePath'   => '/',
            'headers'    => [
                'Content-Type' => 'text/html; charset=UTF-8',
            ],
            'middleware' => [],
        ]);

        $http = new Http(
            $config,
            new Pipeline($this->container),
            new ResponseFactory($config),
            $this->container,
        );

        $http->getPipeline()->pushMiddleware(
            new AuthMiddleware(
                $this->container,
                new TestAuthHttpProvider(),
                new TestAuthHttpStorage(),
                $reg = new TransportRegistry(),
            ),
        );
        $http->getPipeline()->pushMiddleware($firewall);

        $reg->setTransport('transport', $transport);

        return $http;
    }
}
