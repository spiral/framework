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

        self::assertSame('http://localhost/file.jpg', (string) $uri);

        // PHP 8.1 deprecation error fix
        self::assertTrue(
            ($error = error_get_last()) === null ||
            !\str_contains($error['message'], 'Spiral'),
        );
    }

    public function testGuzzleResolveWithPrefix(): void
    {
        $resolver = StaticResolver::create('http://localhost/upload/');

        $uri = $resolver->resolve('file.jpg');

        self::assertSame('http://localhost/upload/file.jpg', (string) $uri);

        // PHP 8.1 deprecation error fix
        self::assertTrue(
            ($error = error_get_last()) === null ||
            !\str_contains($error['message'], 'Spiral'),
        );
    }
}
