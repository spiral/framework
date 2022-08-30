<?php

/**
 * Spiral, Core Components
 *
 * @author Wolfy-J
 */

namespace Spiral\Tests\Tokenizer;

use PHPUnit\Framework\TestCase;
use Spiral\Tokenizer\Config\TokenizerConfig;

class ConfigTest extends TestCase
{
    public function testDirectories()
    {
        $config = new TokenizerConfig([
            'directories' => ['a', 'b', 'c']
        ]);
        $this->assertSame(['a', 'b', 'c'], $config->getDirectories());
    }

    public function testExcluded()
    {
        $config = new TokenizerConfig([
            'exclude' => ['a', 'b', 'c']
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
                ]
            ]
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
                ]
            ]
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
}
