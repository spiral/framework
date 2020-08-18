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
        $core = TestCore::init([
            'root'   => __DIR__,
            'config' => __DIR__ . '/config',
        ]);

        /** @var ContainerInterface $c */
        $c = $core->getContainer();

        ContainerScope::runScope($c, function (): void {
            $this->assertSame(['key' => 'value'], spiral(TestConfig::class)->toArray());
        });
    }

    public function testEnv(): void
    {
        $core = TestCore::init([
            'root'   => __DIR__,
            'config' => __DIR__ . '/config',
        ], new Environment([
            'key' => '(true)',
        ]));

        /** @var ContainerInterface $c */
        $c = $core->getContainer();

        ContainerScope::runScope($c, function (): void {
            $this->assertSame(true, env('key'));
        });
    }

    public function testDirectory(): void
    {
        $core = TestCore::init([
            'root'   => __DIR__,
            'config' => __DIR__ . '/config',
        ]);

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

        $core = TestCore::init([
            'root'   => __DIR__,
            'config' => __DIR__ . '/config',
        ]);

        /** @var ContainerInterface $c */
        $c = $core->getContainer();

        ContainerScope::runScope($c, function (): void {
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
        $path = str_replace(['\\', '//'], '/', $path);
        $this->assertSame(rtrim($path, '/') . '/', $value);
    }
}
