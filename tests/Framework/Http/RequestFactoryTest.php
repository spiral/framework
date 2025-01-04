<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Http;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class RequestFactoryTest extends TestCase
{
    public function testCreateRequest(): void
    {
        $r = (new Psr17Factory())->createServerRequest('GET', '/home');
        self::assertSame('GET', $r->getMethod());
        self::assertSame('/home', $r->getUri()->getPath());
    }
}
