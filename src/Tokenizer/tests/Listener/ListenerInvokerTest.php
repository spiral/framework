<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Listener;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Spiral\Boot\BootloadManager\BootloadManager;
use Spiral\Boot\BootloadManager\Initializer;
use Spiral\Boot\BootloadManager\InitializerInterface;
use Spiral\Boot\BootloadManager\StrategyBasedBootloadManager;
use Spiral\Core\Container;
use Spiral\Tests\Boot\Fixtures\TestCore;
use Spiral\Tokenizer\Bootloader\TokenizerListenerBootloader;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\Listener\ClassesLoaderInterface;
use Spiral\Tokenizer\Listener\ListenerInvoker;
use Spiral\Tests\Tokenizer\Classes\Targets;
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

        $loader = \Mockery::mock(ClassesLoaderInterface::class);
        $loader->shouldReceive('loadClasses')->once()->andReturnFalse();

        $listener = \Mockery::mock(TokenizationListenerInterface::class);
        $listener->shouldReceive('listen')->once();
        $listener->shouldReceive('finalize')->once();

        $container = new Container();
        $container->bind(ClassesInterface::class, $classes);
        $container->bind(ClassesLoaderInterface::class, $loader);

        $kernel = TestCore::create(['root' => __DIR__], true, null, $container);

        $bootloader = new TokenizerListenerBootloader();
        $bootloader->addListener($listener);

        $config = new TokenizerConfig([
            'load' => [
                'classes' => true,
                'enums' => false,
                'interfaces' => false,
            ],
        ]);

        $container->invoke([$bootloader, 'boot'], compact('kernel', 'config'));

        $kernel->run();
    }
}
