<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Bootloader;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Boot\Memory;
use Spiral\Boot\MemoryInterface;
use Spiral\Boot\NullMemory;
use Spiral\Core\FactoryInterface;
use Spiral\Tokenizer\Bootloader\TokenizerListenerBootloader;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\Listener\CachedClassesLoader;
use Spiral\Tokenizer\Listener\ClassesLoaderInterface;

final class TokenizerListenerBootloaderTest extends TestCase
{
    public function testDisableCacheListenersThroughEnv(): void
    {
        $bootloader = new TokenizerListenerBootloader();

        $factory = m::mock(FactoryInterface::class);
        $factory->shouldReceive('make')->once()->withSomeOfArgs(Memory::class)
            ->andReturn($memory = m::mock(MemoryInterface::class));

        $factory->shouldReceive('make')->once()->with(CachedClassesLoader::class, [
            'memory' => $memory,
            'readCache' => false
        ])->andReturn($loader = m::mock(ClassesLoaderInterface::class));

        $dirs = m::mock(DirectoriesInterface::class);

        $env = m::mock(EnvironmentInterface::class);
        $env->shouldReceive('get')->with('TOKENIZER_CACHE_TARGETS', false)->andReturnFalse();
        $config = new TokenizerConfig([
            'cache' => ['directory' => 'cache',],
        ]);

        $this->assertSame(
            $loader,
            $bootloader->initCachedClassesLoader($factory, $dirs, $env, $config),
        );
    }

    public function testEnableCacheListenersThroughEnv(): void
    {
        $bootloader = new TokenizerListenerBootloader();

        $factory = m::mock(FactoryInterface::class);
        $factory->shouldReceive('make')->once()->with(Memory::class, [
            'directory' => 'cache',
        ])->andReturn($memory = m::mock(MemoryInterface::class));

        $factory->shouldReceive('make')->once()->with(CachedClassesLoader::class, [
            'memory' => $memory,
            'readCache' => true
        ])->andReturn($loader = m::mock(ClassesLoaderInterface::class));

        $dirs = m::mock(DirectoriesInterface::class);

        $env = m::mock(EnvironmentInterface::class);
        $env->shouldReceive('get')->with('TOKENIZER_CACHE_TARGETS', false)->andReturnTrue();

        $config = new TokenizerConfig(['cache' => ['directory' => 'cache',]]);

        $this->assertSame(
            $loader,
            $bootloader->initCachedClassesLoader($factory, $dirs, $env, $config),
        );
    }

    public function testEnableCacheListenersThroughConfig(): void
    {
        $bootloader = new TokenizerListenerBootloader();

        $factory = m::mock(FactoryInterface::class);
        $factory->shouldReceive('make')->once()->with(Memory::class, [
            'directory' => 'cache',
        ])->andReturn($memory = m::mock(MemoryInterface::class));

        $factory->shouldReceive('make')->once()->with(CachedClassesLoader::class, [
            'memory' => $memory,
            'readCache' => true
        ])->andReturn($loader = m::mock(ClassesLoaderInterface::class));

        $dirs = m::mock(DirectoriesInterface::class);

        $env = m::mock(EnvironmentInterface::class);
        $env->shouldReceive('get')->with('TOKENIZER_CACHE_TARGETS', true)->andReturnTrue();

        $config = new TokenizerConfig(['cache' => ['directory' => 'cache', 'enabled' => true]]);

        $this->assertSame(
            $loader,
            $bootloader->initCachedClassesLoader($factory, $dirs, $env, $config),
        );
    }

    public function testCacheListenersWithDefaultCacheDir(): void
    {
        $bootloader = new TokenizerListenerBootloader();

        $factory = m::mock(FactoryInterface::class);
        $factory->shouldReceive('make')->once()->with(Memory::class, [
            'directory' => 'runtime/cache/listeners',
        ])->andReturn($memory = m::mock(MemoryInterface::class));

        $factory->shouldReceive('make')->once()->with(CachedClassesLoader::class, [
            'memory' => $memory,
            'readCache' => true
        ])->andReturn($loader = m::mock(ClassesLoaderInterface::class));

        $dirs = m::mock(DirectoriesInterface::class);
        $dirs->shouldReceive('get')->with('runtime')->andReturn('runtime/');

        $env = m::mock(EnvironmentInterface::class);
        $env->shouldReceive('get')->with('TOKENIZER_CACHE_TARGETS', false)->andReturnTrue();

        $config = new TokenizerConfig();

        $this->assertSame(
            $loader,
            $bootloader->initCachedClassesLoader($factory, $dirs, $env, $config),
        );
    }
}