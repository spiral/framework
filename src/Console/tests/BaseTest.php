<?php

declare(strict_types=1);

namespace Spiral\Tests\Console;

use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Console\CommandLocator;
use Spiral\Console\Config\ConsoleConfig;
use Spiral\Console\Console;
use Spiral\Console\LocatorInterface;
use Spiral\Console\StaticLocator;
use Spiral\Tests\Console\Fixtures\User\UserCommand;
use Spiral\Core\Container;
use Spiral\Tokenizer\ScopedClassesInterface;

abstract class BaseTest extends TestCase
{
    public const TOKENIZER_CONFIG = [
        'directories' => [__DIR__ . '/Fixtures/'],
        'exclude'     => ['User'],
    ];

    public const CONFIG = [
        'locateCommands' => false,
        'commands' => [
            UserCommand::class,
        ],
        'interceptors' => []
    ];

    protected Container $container;

    public function setUp(): void
    {
        $this->container = new Container();

        $this->container->bind(
            ConsoleConfig::class,
            new ConsoleConfig(static::CONFIG)
        );
    }

    protected function getCore(LocatorInterface $locator = null, ?EventDispatcherInterface $eventDispatcher = null): Console
    {
        $config = $this->container->get(ConsoleConfig::class);

        return new Console(
            config: $config,
            locator: $locator ?? $this->getStaticLocator([]),
            container: $this->container,
            dispatcher: $eventDispatcher
        );
    }

    protected function getStaticLocator(array $commands): StaticLocator
    {
        return new StaticLocator(
            $commands,
            $this->container->get(ConsoleConfig::class)->getInterceptors(),
            $this->container
        );
    }

    protected function getCommandLocator(ScopedClassesInterface $classes): CommandLocator
    {
        return new CommandLocator(
            $classes,
            $this->container,
            $this->container->get(ConsoleConfig::class)->getInterceptors(),
        );
    }
}
