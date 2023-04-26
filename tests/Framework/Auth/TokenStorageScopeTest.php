<?php

declare(strict_types=1);

namespace Spiral\Tests\Auth;

use PHPUnit\Framework\TestCase;
use Spiral\Auth\TokenInterface;
use Spiral\Auth\TokenStorageInterface;
use Spiral\Auth\TokenStorageScope;
use Spiral\Core\Container;

final class TokenStorageScopeTest extends TestCase
{
    public function testLoad(): void
    {
        $storage = $this->createMock(TokenStorageInterface::class);
        $storage
            ->expects($this->once())
            ->method('load')
            ->with('foo')
            ->willReturn($token = $this->createMock(TokenInterface::class));

        $container = new Container();
        $container->bind(TokenStorageInterface::class, $storage);

        $scope = new TokenStorageScope($container);

        $this->assertSame($token, $scope->load('foo'));
    }

    public function testCreate(): void
    {
        $expiresAt = new \DateTimeImmutable();

        $storage = $this->createMock(TokenStorageInterface::class);
        $storage
            ->expects($this->once())
            ->method('create')
            ->with(['foo' => 'bar'], $expiresAt)
            ->willReturn($token = $this->createMock(TokenInterface::class));

        $container = new Container();
        $container->bind(TokenStorageInterface::class, $storage);

        $scope = new TokenStorageScope($container);

        $this->assertSame($token, $scope->create(['foo' => 'bar'], $expiresAt));
    }

    public function testDelete(): void
    {
        $token = $this->createMock(TokenInterface::class);

        $storage = $this->createMock(TokenStorageInterface::class);
        $storage
            ->expects($this->once())
            ->method('delete')
            ->with($token);

        $container = new Container();
        $container->bind(TokenStorageInterface::class, $storage);

        $scope = new TokenStorageScope($container);

        $scope->delete($token);
    }
}
