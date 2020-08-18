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
use Spiral\Boot\MemoryInterface;
use Spiral\Tests\Boot\Fixtures\TestCore;

class MemoryTest extends TestCase
{
    public function testMemory(): void
    {
        $core = TestCore::init([
            'root'  => __DIR__,
            'cache' => __DIR__ . '/cache'
        ]);

        /** @var MemoryInterface $memory */
        $memory = $core->getContainer()->get(MemoryInterface::class);

        $memory->saveData('test', 'data');
        $this->assertFileExists(__DIR__ . '/cache/test.php');
        $this->assertSame('data', $memory->loadData('test'));

        unlink(__DIR__ . '/cache/test.php');
        $this->assertNull($memory->loadData('test'));
    }

    public function testBroken(): void
    {
        $core = TestCore::init([
            'root'  => __DIR__,
            'cache' => __DIR__ . '/cache'
        ]);

        /** @var MemoryInterface $memory */
        $memory = $core->getContainer()->get(MemoryInterface::class);

        file_put_contents(__DIR__ . '/cache/test.php', '<?php broken');
        $this->assertNull($memory->loadData('test'));

        unlink(__DIR__ . '/cache/test.php');
        $this->assertNull($memory->loadData('test'));
    }
}
