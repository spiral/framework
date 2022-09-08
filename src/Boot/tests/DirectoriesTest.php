<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot;

use PHPUnit\Framework\TestCase;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\Exception\BootException;
use Spiral\Boot\Exception\DirectoryException;
use Spiral\Tests\Boot\Fixtures\TestCore;

class DirectoriesTest extends TestCase
{
    public function testDirectories(): void
    {
        $core = TestCore::create([
            'root' => __DIR__,
        ])->run();

        /**
         * @var DirectoriesInterface $dirs
         */
        $dirs = $core->getContainer()->get(DirectoriesInterface::class);

        $this->assertDir(__DIR__, $dirs->get('root'));

        $this->assertDir(__DIR__ . '/app', $dirs->get('app'));

        $this->assertDir(__DIR__ . '/public', $dirs->get('public'));

        $this->assertDir(__DIR__ . '/app/config', $dirs->get('config'));
        $this->assertDir(__DIR__ . '/app/resources', $dirs->get('resources'));

        $this->assertDir(__DIR__ . '/runtime', $dirs->get('runtime'));
        $this->assertDir(__DIR__ . '/runtime/cache', $dirs->get('cache'));
    }

    public function testKernelException(): void
    {
        $this->expectException(BootException::class);

        TestCore::create([])->run();
    }

    public function testSetDirectory(): void
    {
        $core = TestCore::create([
            'root' => __DIR__,
        ])->run();

        /**
         * @var DirectoriesInterface $dirs
         */
        $dirs = $core->getContainer()->get(DirectoriesInterface::class);
        $dirs->set('alias', __DIR__);

        $this->assertDir(__DIR__, $dirs->get('alias'));
    }

    public function testGetAll(): void
    {
        $core = TestCore::create([
            'root' => __DIR__,
        ])->run();

        /**
         * @var DirectoriesInterface $dirs
         */
        $dirs = $core->getContainer()->get(DirectoriesInterface::class);

        $this->assertFalse($dirs->has('alias'));
        $dirs->set('alias', __DIR__);
        $this->assertTrue($dirs->has('alias'));

        $this->assertCount(8, $dirs->getAll());
    }

    public function testGetException(): void
    {
        $this->expectException(DirectoryException::class);

        $core = TestCore::create([
            'root' => __DIR__,
        ])->run();

        /**
         * @var DirectoriesInterface $dirs
         */
        $dirs = $core->getContainer()->get(DirectoriesInterface::class);
        $dirs->get('alias');
    }

    private function assertDir($path, $value): void
    {
        $path = str_replace(['\\', '//'], '/', $path);
        $this->assertSame(rtrim($path, '/') . '/', $value);
    }
}
