<?php

declare(strict_types=1);

namespace Spiral\Tests\Auth;

use PHPUnit\Framework\TestCase;
use Spiral\Auth\AuthContext;
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
}
