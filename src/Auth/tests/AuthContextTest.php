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
use Spiral\Auth\AuthContext;
use Spiral\Auth\TokenInterface;
use Spiral\Tests\Auth\Stub\TestProvider;
use Spiral\Tests\Auth\Stub\TestToken;

class AuthContextTest extends TestCase
{
    public function testNull(): void
    {
        $context = new AuthContext(new TestProvider());

        $this->assertNull($context->getToken());
        $this->assertNull($context->getActor());
        $this->assertNull($context->getTransport());

        $this->assertFalse($context->isClosed());
    }

    public function testTokenButNoActor(): void
    {
        $context = new AuthContext(new TestProvider());
        $context->start(new TestToken('1', ['ok' => false]), 'cookie');

        $this->assertInstanceOf(TokenInterface::class, $context->getToken());
        $this->assertNull($context->getActor());
        $this->assertSame('cookie', $context->getTransport());
    }

    public function testActor(): void
    {
        $context = new AuthContext(new TestProvider());
        $context->start(new TestToken('1', ['ok' => true]), 'cookie');

        $this->assertInstanceOf(TokenInterface::class, $context->getToken());
        $this->assertInstanceOf(\stdClass::class, $context->getActor());
        $this->assertSame('cookie', $context->getTransport());
    }

    public function testClosed(): void
    {
        $context = new AuthContext(new TestProvider());
        $context->start(new TestToken('1', ['ok' => true]), 'cookie');

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
