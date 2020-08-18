<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Boot;

use PHPUnit\Framework\TestCase;
use Spiral\Tests\Boot\Fixtures\TestConfig;
use Spiral\Tests\Boot\Fixtures\TestCore;

class ConfigsTest extends TestCase
{
    public function testDirectories(): void
    {
        $core = TestCore::init([
            'root'   => __DIR__,
            'config' => __DIR__ . '/config'
        ]);

        /** @var TestConfig $config */
        $config = $core->getContainer()->get(TestConfig::class);

        $this->assertSame(['key' => 'value'], $config->toArray());
    }
}
