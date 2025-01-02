<?php

declare(strict_types=1);

namespace Spiral\Tests\Cookies;

use Defuse\Crypto\Key;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Cookies\Config\CookiesConfig;
use Spiral\Cookies\CookieQueue;
use Spiral\Cookies\Middleware\CookiesMiddleware;
use Spiral\Core\Container;
use Spiral\Core\Options;
use Spiral\Encrypter\Config\EncrypterConfig;
use Spiral\Encrypter\Encrypter;
use Spiral\Encrypter\EncrypterFactory;
use Spiral\Encrypter\EncrypterInterface;
use Spiral\Encrypter\EncryptionInterface;
use Spiral\Http\Config\HttpConfig;
use Spiral\Http\Http;
use Spiral\Http\Pipeline;
use Nyholm\Psr7\ServerRequest;

final class CookiesTest extends TestCase
{
    private Container $container;

    public function setUp(): void
    {
        $options = new Options();
        $options->checkScope = false;
        $this->container = new Container(options: $options);
        $this->container->bind(CookiesConfig::class, new CookiesConfig([
            'domain'   => '.%s',
            'method'   => CookiesConfig::COOKIE_ENCRYPT,
            'excluded' => ['PHPSESSID', 'csrf-token']
        ]));

        $this->container->bind(
            EncrypterFactory::class,
            new EncrypterFactory(new EncrypterConfig([
                'key' => Key::createNewRandomKey()->saveToAsciiSafeString()
            ]))
        );

        $this->container->bind(EncryptionInterface::class, EncrypterFactory::class);
        $this->container->bind(EncrypterInterface::class, Encrypter::class);
    }

    public function testCookieQueueInRequestAttribute(): void
    {
        $core = $this->httpCore([CookiesMiddleware::class]);
        $core->setHandler(function (ServerRequestInterface $r): string {
            $this->assertInstanceOf(CookieQueue::class, $r->getAttribute(CookieQueue::ATTRIBUTE));
            return 'all good';
        });

        $response = $this->get($core, '/');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('all good', (string)$response->getBody());
    }

    public function testSetEncryptedCookie(): void
    {
        $core = $this->httpCore([CookiesMiddleware::class]);
        $core->setHandler(function (ServerRequestInterface $r): string {
            $r->getAttribute(CookieQueue::ATTRIBUTE)->set('name', 'value');

            return 'all good';
        });

        $response = $this->get($core, '/');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('all good', (string)$response->getBody());

        $cookies = $this->fetchCookies($response);
        $this->assertArrayHasKey('name', $cookies);
        $this->assertSame(
            'value',
            $this->container->get(EncrypterInterface::class)->decrypt($cookies['name'])
        );
    }

    public function testSetNotProtectedCookie(): void
    {
        $core = $this->httpCore([CookiesMiddleware::class]);
        $core->setHandler(function (ServerRequestInterface $r): string {
            $r->getAttribute(CookieQueue::ATTRIBUTE)->set('PHPSESSID', 'value');

            return 'all good';
        });

        $response = $this->get($core, '/');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('all good', (string)$response->getBody());

        $cookies = $this->fetchCookies($response);
        $this->assertArrayHasKey('PHPSESSID', $cookies);
        $this->assertSame('value', $cookies['PHPSESSID']);
    }

    public function testDecrypt(): void
    {
        $core = $this->httpCore([CookiesMiddleware::class]);
        $core->setHandler(fn(ServerRequestInterface $r) => $r->getCookieParams()['name']);

        $value = $this->container->get(EncrypterInterface::class)->encrypt('cookie-value');

        $response = $this->get($core, '/', [], [], ['name' => $value]);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('cookie-value', (string)$response->getBody());
    }

    public function testDecryptArray(): void
    {
        $core = $this->httpCore([CookiesMiddleware::class]);
        $core->setHandler(fn(ServerRequestInterface $r) => $r->getCookieParams()['name'][0]);

        $value[] = $this->container->get(EncrypterInterface::class)->encrypt('cookie-value');

        $response = $this->get($core, '/', [], [], ['name' => $value]);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('cookie-value', (string)$response->getBody());
    }

    public function testDecryptBroken(): void
    {
        $core = $this->httpCore([CookiesMiddleware::class]);
        $core->setHandler(fn(ServerRequestInterface $r) => $r->getCookieParams()['name']);

        $value = $this->container->get(EncrypterInterface::class)->encrypt('cookie-value') . 'BROKEN';

        $response = $this->get($core, '/', [], [], ['name' => $value]);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', (string)$response->getBody());
    }

    public function testDelete(): void
    {
        $core = $this->httpCore([CookiesMiddleware::class]);
        $core->setHandler(function (ServerRequestInterface $r): string {
            $r->getAttribute(CookieQueue::ATTRIBUTE)->set('name', 'value');
            $r->getAttribute(CookieQueue::ATTRIBUTE)->delete('name');

            return 'all good';
        });

        $response = $this->get($core, '/');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('all good', (string)$response->getBody());

        $cookies = $this->fetchCookies($response);
        $this->assertArrayHasKey('name', $cookies);
        $this->assertSame('', $cookies['name']);
    }

    public function testUnprotected(): void
    {
        $this->container->bind(CookiesConfig::class, new CookiesConfig([
            'domain'   => '.%s',
            'method'   => CookiesConfig::COOKIE_UNPROTECTED,
            'excluded' => ['PHPSESSID', 'csrf-token']
        ]));

        $core = $this->httpCore([CookiesMiddleware::class]);
        $core->setHandler(function (ServerRequestInterface $r): string {
            $r->getAttribute(CookieQueue::ATTRIBUTE)->set('name', 'value');

            return 'all good';
        });

        $response = $this->get($core, '/');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('all good', (string)$response->getBody());

        $cookies = $this->fetchCookies($response);
        $this->assertArrayHasKey('name', $cookies);
        $this->assertSame('value', $cookies['name']);
    }

    public function testGetUnprotected(): void
    {
        $this->container->bind(CookiesConfig::class, new CookiesConfig([
            'domain'   => '.%s',
            'method'   => CookiesConfig::COOKIE_UNPROTECTED,
            'excluded' => ['PHPSESSID', 'csrf-token']
        ]));

        $core = $this->httpCore([CookiesMiddleware::class]);
        $core->setHandler(fn(ServerRequestInterface $r) => $r->getCookieParams()['name']);

        $value = 'cookie-value';

        $response = $this->get($core, '/', [], [], ['name' => $value]);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('cookie-value', (string)$response->getBody());
    }

    public function testHMAC(): void
    {
        $this->container->bind(CookiesConfig::class, new CookiesConfig([
            'domain'   => '.%s',
            'method'   => CookiesConfig::COOKIE_HMAC,
            'excluded' => ['PHPSESSID', 'csrf-token']
        ]));

        $core = $this->httpCore([CookiesMiddleware::class]);
        $core->setHandler(function (ServerRequestInterface $r): string {
            $r->getAttribute(CookieQueue::ATTRIBUTE)->set('name', 'value');

            return 'all good';
        });

        $response = $this->get($core, '/');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('all good', (string)$response->getBody());

        $cookies = $this->fetchCookies($response);
        $this->assertArrayHasKey('name', $cookies);

        $core->setHandler(fn($r) => $r->getCookieParams()['name']);

        $response = $this->get($core, '/', [], [], $cookies);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('value', (string)$response->getBody());
    }

    private function httpCore(array $middleware = []): Http
    {
        $config = new HttpConfig([
            'basePath'   => '/',
            'headers'    => [
                'Content-Type' => 'text/html; charset=UTF-8'
            ],
            'middleware' => $middleware
        ]);

        return new Http(
            $config,
            new Pipeline($this->container),
            new TestResponseFactory($config),
            $this->container
        );
    }

    private function get(
        Http $core,
        string $uri,
        array $query = [],
        array $headers = [],
        array $cookies = []
    ): ResponseInterface {
        return $core->handle($this->request($uri, 'GET', $query, $headers, $cookies));
    }

    private function request(
        string $uri,
        string $method,
        array $query = [],
        array $headers = [],
        array $cookies = []
    ): ServerRequest {
        $request = new ServerRequest($method, $uri, $headers, 'php://input');

        return $request
            ->withQueryParams($query)
            ->withCookieParams($cookies);
    }

    private function fetchCookies(ResponseInterface $response): array
    {
        $result = [];

        foreach ($response->getHeaders() as $line) {
            $cookie = explode('=', implode('', $line));
            $result[$cookie[0]] = rawurldecode(substr(
                $cookie[1],
                0,
                (int)strpos($cookie[1], ';')
            ));
        }

        return $result;
    }
}
