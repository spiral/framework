<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Bootloader;

use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Boot\Memory;
use Spiral\Boot\MemoryInterface;
use Spiral\Core\Container;
use Spiral\Core\FactoryInterface;
use Spiral\Tests\Tokenizer\Fixtures\TestCoreWithTokenizer;
use Spiral\Tokenizer\Bootloader\TokenizerListenerBootloader;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\Listener\CachedClassesLoader;
use Spiral\Tokenizer\Listener\CachedEnumsLoader;
use Spiral\Tokenizer\Listener\CachedInterfacesLoader;
use Spiral\Tokenizer\Listener\ClassesLoaderInterface;
use Spiral\Tokenizer\Listener\EnumsLoaderInterface;
use Spiral\Tokenizer\Listener\InterfacesLoaderInterface;

final class TokenizerListenerBootloaderTest extends TestCase
{
    public static function loadersProvider(): iterable
    {
        yield 'Classes' => [CachedClassesLoader::class, ClassesLoaderInterface::class, 'initCachedClassesLoader'];
        yield 'Enums' => [CachedEnumsLoader::class, EnumsLoaderInterface::class, 'initCachedEnumsLoader'];
        yield 'Interfaces' => [CachedInterfacesLoader::class, InterfacesLoaderInterface::class, 'initCachedInterfacesLoader'];
    }

    #[DataProvider(methodName: 'loadersProvider')]
    public function testEnableCacheListenersThroughConfig(string $class, string $interface, string $method): void
    {
        $bootloader = new TokenizerListenerBootloader();

        $factory = m::mock(FactoryInterface::class);
        $factory->shouldReceive('make')->times(1)->with(Memory::class, [
            'directory' => 'cache',
        ])->andReturn($memory = m::mock(MemoryInterface::class));

        $factory->shouldReceive('make')->once()->with($class, [
            'memory' => $memory,
            'readCache' => true
        ])->andReturn($loader = m::mock($interface));

        $config = new TokenizerConfig(['cache' => ['directory' => 'cache', 'enabled' => true]]);

        $this->assertSame(
            $loader,
            $bootloader->{$method}($factory, $config),
        );
    }

    public function testAddDirectoryInBootloaderInit(): void
    {
        $container = new Container();

        $kernel = TestCoreWithTokenizer::create(directories: ['root' => __DIR__], container: $container);
        $kernel->run();

        $this->assertTrue(\in_array(
            \dirname(__DIR__) . '/Fixtures/Bootloader',
            $container->get(TokenizerConfig::class)->getDirectories()
        ));
    }

}
