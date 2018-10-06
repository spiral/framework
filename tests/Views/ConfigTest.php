<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Views\Tests;

use PHPUnit\Framework\TestCase;
use Spiral\Views\Config\ViewsConfig;

class ConfigTest extends TestCase
{
    public function testCache()
    {
        $config = new ViewsConfig([
            'cache' => [
                'enable'    => true,
                'directory' => '/tmp'
            ]
        ]);

        $this->assertTrue($config->cacheEnabled());
        $this->assertSame('/tmp/', $config->cacheDirectory());
    }
}