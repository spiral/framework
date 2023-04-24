<?php

declare(strict_types=1);

namespace Spiral\Tests\Auth\Middleware\Firewall;

use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Auth\Middleware\Firewall\OverwriteFirewall;
use Spiral\Auth\Transport\HeaderTransport;

final class OverwriteFirewallTest extends BaseFirewallTestCase
{
    #[DataProvider('successTokensDataProvider')]
    #[DataProvider('failTokensDataProvider')]
    public function testOverwriteFirewall(string $token): void
    {
        $http = $this->getCore(
            new OverwriteFirewall(new Uri('/login')),
            new HeaderTransport()
        );

        $http->setHandler(
            static function (ServerRequestInterface $request, ResponseInterface $response): void {
                echo $request->getUri();
            }
        );

        $response = $http->handle(
            new ServerRequest('GET', new Uri('/admin'), ['X-Auth-Token' => $token], 'php://input')
        );

        $this->assertSame($token === 'ok' ? '/admin' : '/login', (string) $response->getBody());
    }
}
