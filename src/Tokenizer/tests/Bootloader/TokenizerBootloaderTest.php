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
    #[DataProvider('boolValuesDataProvider')]
    public function testCastingReadCacheEnvVariable(mixed $readCache, bool $expected): void
    {
        $binder = m::spy(BinderInterface::class);
        $dirs = m::mock(DirectoriesInterface::class);
        $env = m::mock(EnvironmentInterface::class);

        $env->shouldReceive('get')->once()->with('TOKENIZER_CACHE_TARGETS', false)->andReturn($readCache);
        $env->shouldReceive('get')->once()->with('TOKENIZER_LOAD_CLASSES', true)->andReturn(true);
        $env->shouldReceive('get')->once()->with('TOKENIZER_LOAD_ENUMS', false)->andReturn(false);
        $env->shouldReceive('get')->once()->with('TOKENIZER_LOAD_INTERFACES', false)->andReturn(false);

        $dirs->shouldReceive('get')->once()->with('app')->andReturn('app/');
        $dirs->shouldReceive('get')->once()->with('resources')->andReturn('resources/');
        $dirs->shouldReceive('get')->once()->with('config')->andReturn('config/');
        $dirs->shouldReceive('get')->once()->with('runtime')->andReturn('runtime/');

        $bootloader = new TokenizerBootloader($config = new ConfigManager(new DirectoryLoader('config')));
        $bootloader->init($binder, $dirs, $env);

        $initConfig = $config->getConfig(TokenizerConfig::CONFIG);

        self::assertSame('runtime/cache/listeners', $initConfig['cache']['directory']);
        self::assertSame($expected, $initConfig['cache']['enabled']);
    }

    #[DataProvider('boolValuesDataProvider')]
    public function testCastingLoadClassesEnvVariable(mixed $classes, bool $expected): void
    {
        $dirs = m::mock(DirectoriesInterface::class);
        $env = m::mock(EnvironmentInterface::class);

        $env->shouldReceive('get')->once()->with('TOKENIZER_CACHE_TARGETS', false)->andReturn(false);
        $env->shouldReceive('get')->once()->with('TOKENIZER_LOAD_CLASSES', true)->andReturn($classes);
        $env->shouldReceive('get')->once()->with('TOKENIZER_LOAD_ENUMS', false)->andReturn(false);
        $env->shouldReceive('get')->once()->with('TOKENIZER_LOAD_INTERFACES', false)->andReturn(false);

        $dirs->shouldReceive('get')->once()->with('app')->andReturn('app/');
        $dirs->shouldReceive('get')->once()->with('resources')->andReturn('resources/');
        $dirs->shouldReceive('get')->once()->with('config')->andReturn('config/');
        $dirs->shouldReceive('get')->once()->with('runtime')->andReturn('runtime/');

        $bootloader = new TokenizerBootloader($config = new ConfigManager(new DirectoryLoader('config')));
        $bootloader->init(m::spy(BinderInterface::class), $dirs, $env);

        self::assertSame($expected, $config->getConfig(TokenizerConfig::CONFIG)['load']['classes']);
    }

    #[DataProvider('boolValuesDataProvider')]
    public function testCastingLoadEnumsEnvVariable(mixed $enums, bool $expected): void
    {
        $dirs = m::mock(DirectoriesInterface::class);
        $env = m::mock(EnvironmentInterface::class);

        $env->shouldReceive('get')->once()->with('TOKENIZER_CACHE_TARGETS', false)->andReturn(false);
        $env->shouldReceive('get')->once()->with('TOKENIZER_LOAD_CLASSES', true)->andReturn(false);
        $env->shouldReceive('get')->once()->with('TOKENIZER_LOAD_ENUMS', false)->andReturn($enums);
        $env->shouldReceive('get')->once()->with('TOKENIZER_LOAD_INTERFACES', false)->andReturn(false);

        $dirs->shouldReceive('get')->once()->with('app')->andReturn('app/');
        $dirs->shouldReceive('get')->once()->with('resources')->andReturn('resources/');
        $dirs->shouldReceive('get')->once()->with('config')->andReturn('config/');
        $dirs->shouldReceive('get')->once()->with('runtime')->andReturn('runtime/');

        $bootloader = new TokenizerBootloader($config = new ConfigManager(new DirectoryLoader('config')));
        $bootloader->init(m::spy(BinderInterface::class), $dirs, $env);

        self::assertSame($expected, $config->getConfig(TokenizerConfig::CONFIG)['load']['enums']);
    }

    #[DataProvider('boolValuesDataProvider')]
    public function testCastingLoadInterfacesEnvVariable(mixed $interfaces, bool $expected): void
    {
        $dirs = m::mock(DirectoriesInterface::class);
        $env = m::mock(EnvironmentInterface::class);

        $env->shouldReceive('get')->once()->with('TOKENIZER_CACHE_TARGETS', false)->andReturn(false);
        $env->shouldReceive('get')->once()->with('TOKENIZER_LOAD_CLASSES', true)->andReturn(false);
        $env->shouldReceive('get')->once()->with('TOKENIZER_LOAD_ENUMS', false)->andReturn(false);
        $env->shouldReceive('get')->once()->with('TOKENIZER_LOAD_INTERFACES', false)->andReturn($interfaces);

        $dirs->shouldReceive('get')->once()->with('app')->andReturn('app/');
        $dirs->shouldReceive('get')->once()->with('resources')->andReturn('resources/');
        $dirs->shouldReceive('get')->once()->with('config')->andReturn('config/');
        $dirs->shouldReceive('get')->once()->with('runtime')->andReturn('runtime/');

        $bootloader = new TokenizerBootloader($config = new ConfigManager(new DirectoryLoader('config')));
        $bootloader->init(m::spy(BinderInterface::class), $dirs, $env);

        self::assertSame($expected, $config->getConfig(TokenizerConfig::CONFIG)['load']['interfaces']);
    }

    public static function boolValuesDataProvider(): \Traversable
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
