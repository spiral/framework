<?php

declare(strict_types=1);

namespace Spiral\Tests\Auth\Middleware\Firewall;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Auth\Middleware\Firewall\RedirectFirewall;
use Spiral\Auth\Transport\HeaderTransport;

final class RedirectFirewallTest extends BaseFirewallTestCase
{
    #[DataProvider('successTokensDataProvider')]
    public function testRedirectFirewallWithoutRedirect(string $token): void
    {
        $http = $this->getCore(
            new RedirectFirewall(new Uri('/login'), new Psr17Factory()),
            new HeaderTransport()
        );

        $http->setHandler(
            static function (ServerRequestInterface $request, ResponseInterface $response): void {
                echo 'success login';
            }
        );

        $response = $http->handle(
            new ServerRequest('GET', new Uri('/admin'), ['X-Auth-Token' => $token], 'php://input')
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('success login', (string)$response->getBody());
    }

    #[DataProvider('failTokensDataProvider')]
    public function testRedirectFirewallWithRedirect(string $token): void
    {
        $http = $this->getCore(
            new RedirectFirewall(new Uri('/login'), new Psr17Factory()),
            new HeaderTransport()
        );

        $http->setHandler(
            static function (ServerRequestInterface $request, ResponseInterface $response): void {
                echo 'success login';
            }
        );

        $response = $http->handle(
            new ServerRequest('GET', new Uri('/admin'), ['X-Auth-Token' => $token], 'php://input')
        );

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(['Location' => ['/login']], $response->getHeaders());
        $this->assertSame('', (string) $response->getBody());
    }

    #[DataProvider('failTokensDataProvider')]
    public function testRedirectFirewallWithRedirectAndCode(string $token): void
    {
        $http = $this->getCore(
            new RedirectFirewall(new Uri('/login'), new Psr17Factory(), 301),
            new HeaderTransport()
        );

        $http->setHandler(
            static function (ServerRequestInterface $request, ResponseInterface $response): void {
                echo 'success login';
            }
        );

        $response = $http->handle(
            new ServerRequest('GET', new Uri('/admin'), ['X-Auth-Token' => $token], 'php://input')
        );

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame(['Location' => ['/login']], $response->getHeaders());
        $this->assertSame('', (string) $response->getBody());
    }
}
