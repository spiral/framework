<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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
use Spiral\Encrypter\Config\EncrypterConfig;
use Spiral\Encrypter\Encrypter;
use Spiral\Encrypter\EncrypterFactory;
use Spiral\Encrypter\EncrypterInterface;
use Spiral\Encrypter\EncryptionInterface;
use Spiral\Http\Config\HttpConfig;
use Spiral\Http\Http;
use Spiral\Http\Pipeline;
use Laminas\Diactoros\ServerRequest;

class CookiesTest extends TestCase
{
    private $container;

    public function setUp(): void
    {
        $this->container = new Container();
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

    public function testScope(): void
    {
        $core = $this->httpCore([CookiesMiddleware::class]);
        $core->setHandler(function ($r) {
            $this->assertInstanceOf(
                CookieQueue::class,
                $this->container->get(ServerRequestInterface::class)
                    ->getAttribute(CookieQueue::ATTRIBUTE)
            );

            $this->assertSame(
                $this->container->get(ServerRequestInterface::class)
                    ->getAttribute(CookieQueue::ATTRIBUTE),
                $r->getAttribute(CookieQueue::ATTRIBUTE)
            );

            return 'all good';
        });

        $response = $this->get($core, '/');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('all good', (string)$response->getBody());
    }

    public function testSetEncryptedCookie(): void
    {
        $core = $this->httpCore([CookiesMiddleware::class]);
        $core->setHandler(function ($r) {
            $this->container->get(ServerRequestInterface::class)
                ->getAttribute(CookieQueue::ATTRIBUTE)->set('name', 'value');

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
        $core->setHandler(function ($r) {
            $this->container->get(ServerRequestInterface::class)
                ->getAttribute(CookieQueue::ATTRIBUTE)->set('PHPSESSID', 'value');

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
        $core->setHandler(function ($r) {

            /**
             * @var ServerRequest $r
             */
            return $r->getCookieParams()['name'];
        });

        $value = $this->container->get(EncrypterInterface::class)->encrypt('cookie-value');

        $response = $this->get($core, '/', [], [], ['name' => $value]);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('cookie-value', (string)$response->getBody());
    }

    public function testDecryptArray(): void
    {
        $core = $this->httpCore([CookiesMiddleware::class]);
        $core->setHandler(function ($r) {

            /**
             * @var ServerRequest $r
             */
            return $r->getCookieParams()['name'][0];
        });

        $value[] = $this->container->get(EncrypterInterface::class)->encrypt('cookie-value');

        $response = $this->get($core, '/', [], [], ['name' => $value]);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('cookie-value', (string)$response->getBody());
    }

    public function testDecryptBroken(): void
    {
        $core = $this->httpCore([CookiesMiddleware::class]);
        $core->setHandler(function ($r) {

            /**
             * @var ServerRequest $r
             */
            return $r->getCookieParams()['name'];
        });

        $value = $this->container->get(EncrypterInterface::class)->encrypt('cookie-value') . 'BROKEN';

        $response = $this->get($core, '/', [], [], ['name' => $value]);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', (string)$response->getBody());
    }

    public function testDelete(): void
    {
        $core = $this->httpCore([CookiesMiddleware::class]);
        $core->setHandler(function ($r) {
            $this->container->get(ServerRequestInterface::class)
                ->getAttribute(CookieQueue::ATTRIBUTE)->set('name', 'value');

            $this->container->get(ServerRequestInterface::class)
                ->getAttribute(CookieQueue::ATTRIBUTE)->delete('name');

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
        $core->setHandler(function ($r) {
            $this->container->get(ServerRequestInterface::class)
                ->getAttribute(CookieQueue::ATTRIBUTE)->set('name', 'value');

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
        $core->setHandler(function ($r) {

            /**
             * @var ServerRequest $r
             */
            return $r->getCookieParams()['name'];
        });

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
        $core->setHandler(function ($r) {
            $this->container->get(ServerRequestInterface::class)
                ->getAttribute(CookieQueue::ATTRIBUTE)->set('name', 'value');

            return 'all good';
        });

        $response = $this->get($core, '/');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('all good', (string)$response->getBody());

        $cookies = $this->fetchCookies($response);
        $this->assertArrayHasKey('name', $cookies);

        $core->setHandler(function ($r) {
            return $r->getCookieParams()['name'];
        });

        $response = $this->get($core, '/', [], [], $cookies);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('value', (string)$response->getBody());
    }

    protected function httpCore(array $middleware = []): Http
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

    protected function get(
        Http $core,
        $uri,
        array $query = [],
        array $headers = [],
        array $cookies = []
    ): ResponseInterface {
        return $core->handle($this->request($uri, 'GET', $query, $headers, $cookies));
    }

    protected function request(
        $uri,
        string $method,
        array $query = [],
        array $headers = [],
        array $cookies = []
    ): ServerRequest {
        return new ServerRequest(
            [],
            [],
            $uri,
            $method,
            'php://input',
            $headers,
            $cookies,
            $query
        );
    }

    protected function fetchCookies(ResponseInterface $response)
    {
        $result = [];

        foreach ($response->getHeaders() as $line) {
            $cookie = explode('=', join('', $line));
            $result[$cookie[0]] = rawurldecode(substr(
                (string)$cookie[1],
                0,
                (int)strpos((string)$cookie[1], ';')
            ));
        }

        return $result;
    }
}
