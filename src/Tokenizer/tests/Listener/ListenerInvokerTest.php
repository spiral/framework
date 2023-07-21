<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Listener;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Tokenizer\Bootloader\TokenizerListenerBootloader;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\EnumsInterface;
use Spiral\Tokenizer\InterfacesInterface;
use Spiral\Tokenizer\Listener\ClassesLoaderInterface;
use Spiral\Tokenizer\Listener\EnumsLoaderInterface;
use Spiral\Tokenizer\Listener\InterfacesLoaderInterface;
use Spiral\Tokenizer\Listener\ListenerInvoker;
use Spiral\Tests\Tokenizer\Classes\Targets;
use Spiral\Tests\Tokenizer\Fixtures\TestCore;
use Spiral\Tokenizer\TokenizationListenerInterface;

final class ListenerInvokerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testInvoke(): void
    {
        $invoker = new ListenerInvoker();

        $classes = \array_map(
            fn(string $class) => new \ReflectionClass($class),
            [
                Targets\ConsoleCommand::class,
                Targets\Filter::class,
                Targets\ConsoleCommandInterface::class,
                Targets\HomeController::class,
            ],
        );

        $listener = \Mockery::mock(TokenizationListenerInterface::class);

        foreach ($classes as $class) {
            $listener->shouldReceive('listen')
                ->once()
                ->with($class);
        }

        $listener->shouldReceive('finalize')
            ->never();

        $invoker->invoke($listener, $classes);
    }

    public function testInvokerViaKernel(): void
    {
        $classes = \Mockery::mock(ClassesInterface::class);
        $classes->shouldReceive('getClasses')
            ->once()
            ->andReturn([self::class => new \ReflectionClass($this)]);

        $classesLoader = \Mockery::mock(ClassesLoaderInterface::class);
        $classesLoader->shouldReceive('loadClasses')->once()->andReturnFalse();

        $enums = \Mockery::mock(EnumsInterface::class);
        $enums->shouldReceive('getEnums')->never();

        $enumsLoader = \Mockery::mock(EnumsLoaderInterface::class);
        $enumsLoader->shouldReceive('loadEnums')->never();

        $interfaces = \Mockery::mock(InterfacesInterface::class);
        $interfaces->shouldReceive('getInterfaces')->never();

        $interfacesLoader = \Mockery::mock(InterfacesLoaderInterface::class);
        $interfacesLoader->shouldReceive('loadInterfaces')->never();

        $listener = \Mockery::mock(TokenizationListenerInterface::class);
        $listener->shouldReceive('listen')->once();
        $listener->shouldReceive('finalize')->once();

        $container = new Container();
        $container->bind(ClassesInterface::class, $classes);
        $container->bind(ClassesLoaderInterface::class, $classesLoader);
        $container->bind(EnumsInterface::class, $enums);
        $container->bind(EnumsLoaderInterface::class, $enumsLoader);
        $container->bind(InterfacesInterface::class, $interfaces);
        $container->bind(InterfacesLoaderInterface::class, $interfacesLoader);
        $container->bind(TokenizerConfig::class, new TokenizerConfig([
            'load' => [
                'classes' => true,
                'enums' => false,
                'interfaces' => false,
            ],
        ]));

        $kernel = TestCore::create(directories: ['root' => __DIR__], container: $container);

        $bootloader = $container->get(TokenizerListenerBootloader::class);
        $bootloader->addListener($listener);

        $container->invoke(
            [$bootloader, 'boot'],
            ['kernel' => $kernel, 'config' => $container->get(TokenizerConfig::class)]
        );

        $kernel->run();
    }
}
