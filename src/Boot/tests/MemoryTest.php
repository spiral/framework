<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot;

use PHPUnit\Framework\TestCase;
use Spiral\Boot\MemoryInterface;
use Spiral\Tests\Boot\Fixtures\TestCore;

class MemoryTest extends TestCase
{
    public function testMemory(): void
    {
        $core = TestCore::create([
            'root'  => __DIR__,
            'cache' => __DIR__ . '/cache'
        ])->run();

        /** @var MemoryInterface $memory */
        $memory = $core->getContainer()->get(MemoryInterface::class);

        $memory->saveData('test', 'data');
        self::assertFileExists(__DIR__ . '/cache/test.php');
        self::assertSame('data', $memory->loadData('test'));

        unlink(__DIR__ . '/cache/test.php');
        self::assertNull($memory->loadData('test'));
    }

    public function testBroken(): void
    {
        $core = TestCore::create([
            'root'  => __DIR__,
            'cache' => __DIR__ . '/cache'
        ])->run();

        /** @var MemoryInterface $memory */
        $memory = $core->getContainer()->get(MemoryInterface::class);

        file_put_contents(__DIR__ . '/cache/test.php', '<?php broken');
        self::assertNull($memory->loadData('test'));

        unlink(__DIR__ . '/cache/test.php');
        self::assertNull($memory->loadData('test'));
    }
}
