<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Auth;

use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Auth\AuthContext;
use Spiral\Auth\Event\Authenticated;
use Spiral\Auth\Event\Logout;
use Spiral\Auth\TokenInterface;
use Spiral\Tests\Auth\Stub\TestAuthProvider;
use Spiral\Tests\Auth\Stub\TestAuthToken;

class AuthContextTest extends TestCase
{
    public function testNull(): void
    {
        $context = new AuthContext(new TestAuthProvider());

        $this->assertNull($context->getToken());
        $this->assertNull($context->getActor());
        $this->assertNull($context->getTransport());

        $this->assertFalse($context->isClosed());
    }

    public function testTokenButNoActor(): void
    {
        $context = new AuthContext(new TestAuthProvider());
        $context->start(new TestAuthToken('1', ['ok' => false]), 'cookie');

        $this->assertInstanceOf(TokenInterface::class, $context->getToken());
        $this->assertNull($context->getActor());
        $this->assertSame('cookie', $context->getTransport());
    }

    public function testActor(): void
    {
        $context = new AuthContext(new TestAuthProvider());
        $context->start(new TestAuthToken('ok', ['ok' => true]), 'cookie');

        $this->assertInstanceOf(TokenInterface::class, $context->getToken());
        $this->assertInstanceOf(\stdClass::class, $context->getActor());
        $this->assertSame('cookie', $context->getTransport());
    }

    public function testClosed(): void
    {
        $context = new AuthContext(new TestAuthProvider());
        $context->start(new TestAuthToken('1', ['ok' => true]), 'cookie');

        $this->assertInstanceOf(TokenInterface::class, $context->getToken());
        $this->assertInstanceOf(\stdClass::class, $context->getActor());
        $this->assertSame('cookie', $context->getTransport());

        $context->close();

        $this->assertInstanceOf(TokenInterface::class, $context->getToken());
        $this->assertNull($context->getActor());
        $this->assertSame('cookie', $context->getTransport());
        $this->assertTrue($context->isClosed());
    }

    public function testAuthenticatedEventShouldBeDispatched(): void
    {
        $token = new TestAuthToken('1', ['ok' => true]);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with(new Authenticated($token, 'cookie'));

        $context = new AuthContext(new TestAuthProvider(), $dispatcher);
        $context->start($token, 'cookie');
    }

    public function testLogoutEventShouldBeDispatched(): void
    {
        $token = new TestAuthToken('1', ['ok' => true]);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with(new Logout($token));

        $context = new AuthContext(new TestAuthProvider(), $dispatcher);

        (new \ReflectionProperty($context, 'actor'))->setValue($context, $token);

        $context->close();
    }
}
