<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Framework\Http;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\TransactionInterface;
use Spiral\App\User\User;
use Spiral\Auth\Cycle\Token;
use Spiral\Encrypter\EncrypterFactory;
use Spiral\Framework\HttpTest;
use Spiral\Http\Http;

class AuthCycleTest extends HttpTest
{
    public function setUp(): void
    {
        $this->app = $this->makeApp();
        $key = $this->app->get(EncrypterFactory::class)->generateKey();

        $this->app = $this->makeApp([
            'ENCRYPTER_KEY' => $key,
            'CYCLE_AUTH'    => true
        ]);

        $this->app->console()->run('cycle:sync');

        $this->http = $this->app->get(Http::class);
    }

    public function testNoToken(): void
    {
        $this->assertSame(
            'none',
            (string)$this->get('/auth/token')->getBody()
        );
    }

    public function testLogin(): void
    {
        $result = $this->get('/auth/login');

        $this->assertSame('OK', (string)$result->getBody());

        $cookies = $this->fetchCookies($result->getHeader('Set-Cookie'));
        $this->assertTrue(isset($cookies['token']));

        $token = $this->app->get(ORMInterface::class)->getRepository(Token::class)->findOne();

        $this->assertSame(['userID' => 1], $token->getPayload());

        $result = $this->get('/auth/token', [], [], $cookies);

        $this->assertNotSame('none', (string)$result->getBody());
    }

    public function testGetActorNone(): void
    {
        $result = $this->get('/auth/login');

        $this->assertSame('OK', (string)$result->getBody());

        $cookies = $this->fetchCookies($result->getHeader('Set-Cookie'));
        $this->assertTrue(isset($cookies['token']));

        $token = $this->app->get(ORMInterface::class)->getRepository(Token::class)->findOne();

        $this->assertSame(['userID' => 1], $token->getPayload());

        $result = $this->get('/auth/actor', [], [], $cookies);

        $this->assertSame('none', (string)$result->getBody());
    }

    public function testGetActorReal(): void
    {
        $result = $this->get('/auth/login');

        $this->assertSame('OK', (string)$result->getBody());

        $cookies = $this->fetchCookies($result->getHeader('Set-Cookie'));
        $this->assertTrue(isset($cookies['token']));

        $token = $this->app->get(ORMInterface::class)->getRepository(Token::class)->findOne();

        $this->assertSame(['userID' => 1], $token->getPayload());

        $user = new User('Antony');
        $user->id = 1;
        $this->app->get(TransactionInterface::class)->persist($user)->run();

        $result = $this->get('/auth/actor', [], [], $cookies);
        $this->assertSame('Antony', (string)$result->getBody());
    }

    public function testLogout(): void
    {
        $result = $this->get('/auth/login');

        $this->assertSame('OK', (string)$result->getBody());

        $cookies = $this->fetchCookies($result->getHeader('Set-Cookie'));
        $this->assertTrue(isset($cookies['token']));

        $result = $this->get('/auth/token', [], [], $cookies);
        $this->assertNotSame('none', (string)$result->getBody());

        $result = $this->get('/auth/logout', [], [], $cookies);
        $this->assertSame('closed', (string)$result->getBody());

        $result = $this->get('/auth/token', [], [], $cookies);
        $this->assertSame('none', (string)$result->getBody());
    }

    public function testLoginScope(): void
    {
        $result = $this->get('/auth/login2');

        $this->assertSame('OK', (string)$result->getBody());

        $cookies = $this->fetchCookies($result->getHeader('Set-Cookie'));
        $this->assertTrue(isset($cookies['token']));

        $token = $this->app->get(ORMInterface::class)->getRepository(Token::class)->findOne();

        $this->assertSame(['userID' => 1], $token->getPayload());

        $result = $this->get('/auth/token2', [], [], $cookies);

        $this->assertNotSame('none', (string)$result->getBody());
    }

    public function testLoginPayload(): void
    {
        $result = $this->get('/auth/login2');

        $this->assertSame('OK', (string)$result->getBody());

        $cookies = $this->fetchCookies($result->getHeader('Set-Cookie'));
        $this->assertTrue(isset($cookies['token']));

        $result = $this->get('/auth/token3', [], [], $cookies);

        $this->assertSame('{"userID":1}', (string)$result->getBody());
    }
}
