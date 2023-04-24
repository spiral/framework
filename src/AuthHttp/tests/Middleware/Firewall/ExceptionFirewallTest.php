<?php

declare(strict_types=1);

namespace Spiral\Tests\Auth\Middleware\Firewall;

use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Auth\Exception\AuthException;
use Spiral\Auth\Middleware\Firewall\ExceptionFirewall;
use Spiral\Auth\Transport\HeaderTransport;

final class ExceptionFirewallTest extends BaseFirewallTestCase
{
    #[DataProvider('successTokensDataProvider')]
    public function testExceptionFirewallNotThrowException(string $token): void
    {
        $http = $this->getCore(
            new ExceptionFirewall(new AuthException()),
            new HeaderTransport()
        );


        $http->setHandler(
            static function (ServerRequestInterface $request, ResponseInterface $response): void {
                echo 'OK';
            }
        );

        $response = $http->handle(new ServerRequest('GET', '', ['X-Auth-Token' => $token], 'php://input'));

        $this->assertSame('OK', (string) $response->getBody());
    }

    #[DataProvider('failTokensDataProvider')]
    public function testExceptionFirewallThrowException(string $token): void
    {
        $http = $this->getCore(
            new ExceptionFirewall(new AuthException('no user')),
            new HeaderTransport()
        );

        $http->setHandler(
            static function (ServerRequestInterface $request, ResponseInterface $response): void {
                echo 'OK';
            }
        );

        $this->expectException(AuthException::class);
        $response = $http->handle(new ServerRequest('GET', '', ['X-Auth-Token' => $token], 'php://input'));

        $this->assertSame('OK', (string) $response->getBody());
    }
}
