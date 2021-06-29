<?php

declare(strict_types=1);

namespace Spiral\Tests\Distribution\Resolver;

use Spiral\Distribution\Resolver\StaticResolver;
use Spiral\Tests\Distribution\TestCase;

class StaticResolverTest extends TestCase
{
    public function testGuzzleResolve(): void
    {
        $resolver = StaticResolver::create('http://localhost');

        $uri = $resolver->resolve('file.jpg');

        self::assertSame('http://localhost/file.jpg', (string)$uri);
        self::assertNull(error_get_last());
    }

    public function testGuzzleResolveWithPrefix(): void
    {
        $resolver = StaticResolver::create('http://localhost/upload/');

        $uri = $resolver->resolve('file.jpg');

        self::assertSame('http://localhost/upload/file.jpg', (string)$uri);
        self::assertNull(error_get_last());
    }
}
