<?php

/**
 * Spiral Framework, SpiralScout LLC.
 *
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Console;

use PHPUnit\Framework\TestCase;
use Spiral\Console\Config\ConsoleConfig;
use Spiral\Console\Console;
use Spiral\Console\LocatorInterface;
use Spiral\Console\StaticLocator;
use Spiral\Tests\Console\Fixtures\User\UserCommand;
use Spiral\Core\Container;

abstract class BaseTest extends TestCase
{
    public const TOKENIZER_CONFIG = [
        'directories' => [__DIR__ . '/Fixtures/'],
        'exclude'     => ['User'],
    ];

    public const CONFIG = [
        'locateCommands' => false,
        'commands'       => [
            UserCommand::class
        ]
    ];
    protected $container;

    public function setUp(): void
    {
        $this->container = new Container();

        $this->container->bind(
            ConsoleConfig::class,
            new ConsoleConfig(static::CONFIG)
        );
    }

    protected function getCore(LocatorInterface $locator = null): Console
    {
        return new Console(
            $this->container->get(ConsoleConfig::class),
            $locator ?? new StaticLocator([], $this->container),
            $this->container
        );
    }
}
