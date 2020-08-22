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
}
