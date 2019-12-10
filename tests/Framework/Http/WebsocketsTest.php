<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Framework\Http;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Auth\AuthContextInterface;
use Spiral\Encrypter\Encrypter;
use Spiral\Encrypter\EncrypterFactory;
use Spiral\Framework\HttpTest;
use Spiral\Http\Http;

class WebsocketsTest extends HttpTest
{
    public function testBypass(): void
    {
        $this->init();
        $this->assertSame('Hello, Dave.', (string)$this->get('/index')->getBody());
    }

    public function testEmptyServerOK(): void
    {
        $this->init();

        $this->assertSame(403, $this->get('/ws/')->getStatusCode());
        $this->assertSame(200, $this->getWithAttributes('/ws/', [
            'ws:joinServer' => true
        ])->getStatusCode());
    }

    public function testAllowServerAccess(): void
    {
        $this->init(static function () {
            return true;
        });

        $this->assertSame(403, $this->get('/ws/')->getStatusCode());
        $this->assertSame(200, $this->getWithAttributes('/ws/', [
            'ws:joinServer' => true
        ])->getStatusCode());
    }

    public function testDenyServerAccess(): void
    {
        $this->init(static function () {
            return false;
        });

        $this->assertSame(403, $this->get('/ws/')->getStatusCode());
        $this->assertSame(403, $this->getWithAttributes('/ws/', [
            'ws:joinServer' => true
        ])->getStatusCode());
    }

    public function testServerAccessWithDependency(): void
    {
        $this->init(static function (ServerRequestInterface $request) {
            return $request instanceof ServerRequestInterface;
        });

        $this->assertSame(403, $this->get('/ws/')->getStatusCode());
        $this->assertSame(200, $this->getWithAttributes('/ws/', [
            'ws:joinServer' => true
        ])->getStatusCode());
    }

    public function testServerAccessFunction(): void
    {
        $this->init('closelog');

        $this->assertSame(403, $this->get('/ws/')->getStatusCode());
        $this->assertSame(200, $this->getWithAttributes('/ws/', [
            'ws:joinServer' => true
        ])->getStatusCode());
    }

    public function testServerAccessCallback(): void
    {
        $this->init([$this, 'ok']);

        $this->assertSame(403, $this->get('/ws/')->getStatusCode());
        $this->assertSame(200, $this->getWithAttributes('/ws/', [
            'ws:joinServer' => true
        ])->getStatusCode());
    }

    public function ok(ServerRequestInterface $request)
    {
        return $request instanceof ServerRequestInterface;
    }

    public function testServerAccessStaticCallback(): void
    {
        $this->init([self::class, 'ok2']);

        $this->assertSame(403, $this->get('/ws/')->getStatusCode());
        $this->assertSame(200, $this->getWithAttributes('/ws/', [
            'ws:joinServer' => true
        ])->getStatusCode());
    }

    public static function ok2(ServerRequestInterface $request)
    {
        return $request instanceof ServerRequestInterface;
    }

    public function testTopicAccessUndefined(): void
    {
        $this->init(null);

        $this->assertSame(403, $this->get('/ws/')->getStatusCode());
        $this->assertSame(403, $this->getWithAttributes('/ws/', [
            'ws:joinTopics' => 'topic'
        ])->getStatusCode());
    }

    public function testTopicAccessOK(): void
    {
        $this->init(null, function () {
            return true;
        });

        $this->assertSame(403, $this->get('/ws/')->getStatusCode());
        $this->assertSame(200, $this->getWithAttributes('/ws/', [
            'ws:joinTopics' => 'topic'
        ])->getStatusCode());
    }

    public function testTopicWildcardAccessFail(): void
    {
        $this->init(null, function () {
            return true;
        });

        $this->assertSame(403, $this->get('/ws/')->getStatusCode());
        $this->assertSame(403, $this->getWithAttributes('/ws/', [
            'ws:joinTopics' => 'wildcard.1,topic'
        ])->getStatusCode());
    }

    public function testTopicWildcardAccessOK(): void
    {
        $this->init(
            null,
            null,
            function () {
                return true;
            }
        );

        $this->assertSame(403, $this->get('/ws/')->getStatusCode());
        $this->assertSame(200, $this->getWithAttributes('/ws/', [
            'ws:joinTopics' => 'wildcard.1'
        ])->getStatusCode());
    }

    public function testTopicWildcardAccessOK1(): void
    {
        $this->init(
            null,
            null,
            function ($id) {
                return $id === '1';
            }
        );

        $this->assertSame(403, $this->get('/ws/')->getStatusCode());
        $this->assertSame(200, $this->getWithAttributes('/ws/', [
            'ws:joinTopics' => 'wildcard.1'
        ])->getStatusCode());
    }

    public function testTopicWildcardAccessFail2(): void
    {
        $this->init(
            null,
            null,
            function ($id) {
                return $id === '1';
            }
        );

        $this->assertSame(403, $this->get('/ws/')->getStatusCode());
        $this->assertSame(403, $this->getWithAttributes('/ws/', [
            'ws:joinTopics' => 'wildcard.2'
        ])->getStatusCode());
    }

    public function testNonAuthorized(): void
    {
        $this->app = $this->makeApp();
        $key = $this->app->get(EncrypterFactory::class)->generateKey();

        $this->app = $this->makeApp([
            'ENCRYPTER_KEY'              => $key,
            'CYCLE_AUTH'                 => true,
            'RR_BROADCAST_PATH'          => '/ws/',
            'WS_SERVER_CALLBACK'         => null,
            'WS_TOPIC_CALLBACK'          => function (AuthContextInterface $authContext) {
                return $authContext->getToken() !== null;
            },
            'WS_TOPIC_WILDCARD_CALLBACK' => null,
        ]);

        $this->app->console()->run('cycle:sync');
        $this->http = $this->app->get(Http::class);

        $result = $this->get('/auth/login');
        $this->assertSame('OK', (string)$result->getBody());

        $cookies = $this->fetchCookies($result->getHeader('Set-Cookie'));
        $this->assertTrue(isset($cookies['token']));

        $token = $this->app->get(Encrypter::class)->decrypt($cookies['token']);

        $this->assertSame(403, $this->get('/ws/')->getStatusCode());
        $this->assertSame(403, $this->getWithAttributes('/ws/', [
            'ws:joinTopics' => 'topic'
        ])->getStatusCode());

        $this->assertSame(200, $this->getWithAttributes('/ws/', [
            'ws:joinTopics' => 'topic'
        ], [
            'x-auth-token' => $token
        ])->getStatusCode());
    }

    private function init(callable $server = null, callable $topic = null, callable $topicWildcard = null): void
    {
        $this->app = $this->makeApp([
            'RR_BROADCAST_PATH'          => '/ws/',
            'WS_SERVER_CALLBACK'         => $server,
            'WS_TOPIC_CALLBACK'          => $topic,
            'WS_TOPIC_WILDCARD_CALLBACK' => $topicWildcard,
        ]);

        $this->http = $this->app->get(Http::class);
    }
}
