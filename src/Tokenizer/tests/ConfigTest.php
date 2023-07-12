<?php

namespace Spiral\Tests\Tokenizer;

use PHPUnit\Framework\TestCase;
use Spiral\Tokenizer\Config\TokenizerConfig;

class ConfigTest extends TestCase
{
    public function testDebugNotSet()
    {
        $config = new TokenizerConfig([]);

        $this->assertFalse($config->isDebug());
    }

    public function testDebug()
    {
        $config = new TokenizerConfig(['debug' => false]);
        $this->assertFalse($config->isDebug());

        $config = new TokenizerConfig(['debug' => true]);
        $this->assertTrue($config->isDebug());

        $config = new TokenizerConfig(['debug' => 1]);
        $this->assertTrue($config->isDebug());
    }

    public function testDirectories()
    {
        $config = new TokenizerConfig([
            'directories' => ['a', 'b', 'c'],
        ]);
        $this->assertSame(['a', 'b', 'c'], $config->getDirectories());
    }

    public function testExcluded()
    {
        $config = new TokenizerConfig([
            'exclude' => ['a', 'b', 'c'],
        ]);
        $this->assertSame(['a', 'b', 'c'], $config->getExcludes());
    }

    public function testNonExistScopeShouldReturnDefaultDirectories()
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

        $this->assertSame([
            'directories' => ['a'],
            'exclude' => ['b'],
        ], $config->getScope('bar'));
    }

    public function testExistsScopeShouldReturnDirectoriesFromIt()
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

        $this->assertSame([
            'directories' => ['c'],
            'exclude' => ['d'],
        ], $config->getScope('foo'));

        $this->assertSame([
            'directories' => ['c'],
            'exclude' => ['b'],
        ], $config->getScope('bar'));

        $this->assertSame([
            'directories' => ['a'],
            'exclude' => ['d'],
        ], $config->getScope('baz'));
    }

    public function testGetsCacheDirectory(): void
    {
        $config = new TokenizerConfig();
        $this->assertNull($config->getCacheDirectory());

        $config = new TokenizerConfig([
            'cache' => [
                'directory' => 'foo',
            ],
        ]);

        $this->assertSame('foo', $config->getCacheDirectory());
    }

    public function testCacheEnabled(): void
    {
        $config = new TokenizerConfig();
        $this->assertFalse($config->isCacheEnabled());

        $config = new TokenizerConfig([
            'cache' => [
                'enabled' => true,
            ],
        ]);
        $this->assertTrue($config->isCacheEnabled());
    }

    public function testLoadClassesEnabled(): void
    {
        $config = new TokenizerConfig();
        $this->assertTrue($config->isLoadClassesEnabled()); // by default

        $config = new TokenizerConfig([
            'load' => [
                'classes' => false,
            ],
        ]);
        $this->assertFalse($config->isLoadClassesEnabled());
    }

    public function testLoadEnumsEnabled(): void
    {
        $config = new TokenizerConfig();
        $this->assertFalse($config->isLoadEnumsEnabled()); // by default

        $config = new TokenizerConfig([
            'load' => [
                'enums' => true,
            ],
        ]);
        $this->assertTrue($config->isLoadEnumsEnabled());
    }

    public function testLoadInterfacesEnabled(): void
    {
        $config = new TokenizerConfig();
        $this->assertFalse($config->isLoadInterfacesEnabled()); // by default

        $config = new TokenizerConfig([
            'load' => [
                'interfaces' => true,
            ],
        ]);
        $this->assertTrue($config->isLoadInterfacesEnabled());
    }
}
