<?php

namespace Spiral\Tests\Router;

use PHPUnit\Framework\TestCase;
use Spiral\Router\Registry\DefaultPatternRegistry;

final class DefaultPatternRegistryTest extends TestCase
{
    private const DEFAULT_PATTERNS = [
        'int' => '\d+',
        'integer' => '\d+',
        'uuid' => '[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}',
    ];

    public function testAll(): void
    {
        $registry = new DefaultPatternRegistry();

        $registry->register('foo', '\d+');
        $registry->register('bar', '\d+');

        self::assertSame(self::DEFAULT_PATTERNS + [
            'foo' => '\d+',
            'bar' => '\d+'
        ], $registry->all());
    }
}
