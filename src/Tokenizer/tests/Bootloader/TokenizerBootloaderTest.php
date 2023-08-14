<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Bootloader;

use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Config\ConfigManager;
use Spiral\Config\Loader\DirectoryLoader;
use Spiral\Core\BinderInterface;
use Spiral\Tokenizer\Bootloader\TokenizerBootloader;
use Spiral\Tokenizer\Config\TokenizerConfig;

final class TokenizerBootloaderTest extends TestCase
{
    #[DataProvider('readCacheDataProvider')]
    public function testCastingReadCacheEnvVariable(mixed $readCache, bool $expected): void
    {
        $binder = m::spy(BinderInterface::class);
        $dirs = m::mock(DirectoriesInterface::class);
        $env = m::mock(EnvironmentInterface::class);

        $env->shouldReceive('get')->once()->with('TOKENIZER_CACHE_TARGETS', false)->andReturn($readCache);

        $dirs->shouldReceive('get')->once()->with('app')->andReturn('app/');
        $dirs->shouldReceive('get')->once()->with('resources')->andReturn('resources/');
        $dirs->shouldReceive('get')->once()->with('config')->andReturn('config/');
        $dirs->shouldReceive('get')->once()->with('runtime')->andReturn('runtime/');

        $bootloader = new TokenizerBootloader($config = new ConfigManager(new DirectoryLoader('config')));
        $bootloader->init($binder, $dirs, $env);

        $initConfig = $config->getConfig(TokenizerConfig::CONFIG);

        $this->assertSame('runtime/cache/listeners', $initConfig['cache']['directory']);
        $this->assertSame($expected, $initConfig['cache']['enabled']);
    }

    public static function readCacheDataProvider(): \Traversable
    {
        yield [true, true];
        yield [false, false];
        yield [1, true];
        yield [0, false];
        yield ['1', true];
        yield ['0', false];
        yield ['true', true];
        yield ['false', false];
    }
}
