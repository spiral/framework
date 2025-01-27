<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Spiral\Boot\Environment;
use Spiral\Core\Exception\ScopeException;
use Spiral\Tests\Boot\Fixtures\TestConfig;
use Spiral\Tests\Boot\Fixtures\TestCore;
use Spiral\Core\ContainerScope;

class FunctionsTest extends TestCase
{
    public function testSpiral(): void
    {
        $core = TestCore::create([
            'root'   => __DIR__,
            'config' => __DIR__ . '/config',
        ])->run();

        /** @var ContainerInterface $c */
        $c = $core->getContainer();

        ContainerScope::runScope($c, static function (): void {
            self::assertSame(['key' => 'value'], spiral(TestConfig::class)->toArray());
        });
    }

    public function testEnv(): void
    {
        $core = TestCore::create([
            'root'   => __DIR__,
            'config' => __DIR__ . '/config',
        ])->run(new Environment([
            'key' => '(true)',
        ]));

        /** @var ContainerInterface $c */
        $c = $core->getContainer();

        ContainerScope::runScope($c, static function (): void {
            self::assertTrue(env('key'));
        });
    }

    public function testDirectory(): void
    {
        $core = TestCore::create([
            'root'   => __DIR__,
            'config' => __DIR__ . '/config',
        ])->run();

        /** @var ContainerInterface $c */
        $c = $core->getContainer();

        ContainerScope::runScope($c, function (): void {
            $this->assertDir(__DIR__ . '/config', directory('config'));
        });
    }

    public function testSpiralException(): void
    {
        $this->expectException(ScopeException::class);

        spiral(TestConfig::class);
    }

    public function testSpiralException2(): void
    {
        $this->expectException(ScopeException::class);

        $core = TestCore::create([
            'root'   => __DIR__,
            'config' => __DIR__ . '/config',
        ])->run();

        /** @var ContainerInterface $c */
        $c = $core->getContainer();

        ContainerScope::runScope($c, static function (): void {
            spiral(Invalid::class);
        });
    }

    public function testEnvException(): void
    {
        $this->expectException(ScopeException::class);

        env('key');
    }

    public function testDirectoryException(): void
    {
        $this->expectException(ScopeException::class);

        directory('key');
    }

    private function assertDir($path, $value): void
    {
        $path = \str_replace(['\\', '//'], '/', $path);
        self::assertSame(\rtrim($path, '/') . '/', $value);
    }
}
