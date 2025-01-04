<?php

namespace Spiral\Tests\Tokenizer;

use PHPUnit\Framework\TestCase;
use Spiral\Tokenizer\Config\TokenizerConfig;

class ConfigTest extends TestCase
{
    public function testDebugNotSet(): void
    {
        $config = new TokenizerConfig([]);

        self::assertFalse($config->isDebug());
    }

    public function testDebug(): void
    {
        $config = new TokenizerConfig(['debug' => false]);
        self::assertFalse($config->isDebug());

        $config = new TokenizerConfig(['debug' => true]);
        self::assertTrue($config->isDebug());

        $config = new TokenizerConfig(['debug' => 1]);
        self::assertTrue($config->isDebug());
    }

    public function testDirectories(): void
    {
        $config = new TokenizerConfig([
            'directories' => ['a', 'b', 'c'],
        ]);
        self::assertSame(['a', 'b', 'c'], $config->getDirectories());
    }

    public function testExcluded(): void
    {
        $config = new TokenizerConfig([
            'exclude' => ['a', 'b', 'c'],
        ]);
        self::assertSame(['a', 'b', 'c'], $config->getExcludes());
    }

    public function testNonExistScopeShouldReturnDefaultDirectories(): void
    {
        $config = new TokenizerConfig([
            'directories' => ['a'],
            'exclude' => ['b'],
            'scopes' => [
                'foo' => [
                    'directories' => ['c'],
                    'exclude' => ['d'],
                ],
            ],
        ]);

        self::assertSame([
            'directories' => ['a'],
            'exclude' => ['b'],
        ], $config->getScope('bar'));
    }

    public function testExistsScopeShouldReturnDirectoriesFromIt(): void
    {
        $config = new TokenizerConfig([
            'directories' => ['a'],
            'exclude' => ['b'],
            'scopes' => [
                'foo' => [
                    'directories' => ['c'],
                    'exclude' => ['d'],
                ],
                'bar' => [
                    'directories' => ['c'],
                ],
                'baz' => [
                    'exclude' => ['d'],
                ],
            ],
        ]);

        self::assertSame([
            'directories' => ['c'],
            'exclude' => ['d'],
        ], $config->getScope('foo'));

        self::assertSame([
            'directories' => ['c'],
            'exclude' => ['b'],
        ], $config->getScope('bar'));

        self::assertSame([
            'directories' => ['a'],
            'exclude' => ['d'],
        ], $config->getScope('baz'));
    }

    public function testGetsCacheDirectory(): void
    {
        $config = new TokenizerConfig();
        self::assertNull($config->getCacheDirectory());

        $config = new TokenizerConfig([
            'cache' => [
                'directory' => 'foo',
            ],
        ]);

        self::assertSame('foo', $config->getCacheDirectory());
    }

    public function testCacheEnabled(): void
    {
        $config = new TokenizerConfig();
        self::assertFalse($config->isCacheEnabled());

        $config = new TokenizerConfig([
            'cache' => [
                'enabled' => true,
            ],
        ]);
        self::assertTrue($config->isCacheEnabled());
    }

    public function testLoadClassesEnabled(): void
    {
        $config = new TokenizerConfig();
        self::assertTrue($config->isLoadClassesEnabled()); // by default

        $config = new TokenizerConfig([
            'load' => [
                'classes' => false,
            ],
        ]);
        self::assertFalse($config->isLoadClassesEnabled());
    }

    public function testLoadEnumsEnabled(): void
    {
        $config = new TokenizerConfig();
        self::assertFalse($config->isLoadEnumsEnabled()); // by default

        $config = new TokenizerConfig([
            'load' => [
                'enums' => true,
            ],
        ]);
        self::assertTrue($config->isLoadEnumsEnabled());
    }

    public function testLoadInterfacesEnabled(): void
    {
        $config = new TokenizerConfig();
        self::assertFalse($config->isLoadInterfacesEnabled()); // by default

        $config = new TokenizerConfig([
            'load' => [
                'interfaces' => true,
            ],
        ]);
        self::assertTrue($config->isLoadInterfacesEnabled());
    }
}
