<?php

declare(strict_types=1);

namespace Spiral\Console\Bootloader;

use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Command\CleanCommand;
use Spiral\Command\PublishCommand;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Config\Patch\Prepend;
use Spiral\Console\CommandLocator;
use Spiral\Console\Config\ConsoleConfig;
use Spiral\Console\Console;
use Spiral\Console\ConsoleDispatcher;
use Spiral\Console\LocatorInterface;
use Spiral\Console\Sequence\CallableSequence;
use Spiral\Console\Sequence\CommandSequence;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\FactoryInterface;
use Spiral\Tokenizer\Bootloader\TokenizerBootloader;

/**
 * Bootloads console and provides ability to register custom bootload commands.
 */
final class ConsoleBootloader extends Bootloader implements SingletonInterface
{
    protected const DEPENDENCIES = [
        TokenizerBootloader::class,
    ];

    protected const SINGLETONS = [
        Console::class => Console::class,
        LocatorInterface::class => CommandLocator::class,
    ];

    public function __construct(
        private readonly ConfiguratorInterface $config
    ) {
    }

    public function init(AbstractKernel $kernel, FactoryInterface $factory): void
    {
        // Lowest priority
        $kernel->started(static function (AbstractKernel $kernel) use ($factory): void {
            $kernel->addDispatcher($factory->make(ConsoleDispatcher::class));
        });

        $this->config->setDefaults(
            ConsoleConfig::CONFIG,
            [
                'commands' => [],
                'sequences' => [],
            ]
        );

        $this->addCommand(CleanCommand::class);
        $this->addCommand(PublishCommand::class);
    }

    /**
     * @param class-string<\Symfony\Component\Console\Command\Command> $command
     * @param bool $lowPriority A low priority command will be overwritten in a name conflict case.
     *        The parameter might be removed in the next major update.
     */
    public function addCommand(string $command, bool $lowPriority = false): void
    {
        $this->config->modify(
            ConsoleConfig::CONFIG,
            $lowPriority
                ? new Prepend('commands', null, $command)
                : new Append('commands', null, $command)
        );
    }

    public function addConfigureSequence(
        string|array|\Closure $sequence,
        string $header,
        string $footer = '',
        array $options = []
    ): void {
        $this->addSequence('configure', $sequence, $header, $footer, $options);
    }

    public function addUpdateSequence(
        string|array|\Closure $sequence,
        string $header,
        string $footer = '',
        array $options = []
    ): void {
        $this->addSequence('update', $sequence, $header, $footer, $options);
    }

    public function addSequence(
        string $name,
        string|array|\Closure $sequence,
        string $header,
        string $footer = '',
        array $options = []
    ): void {
        $this->config->modify(
            ConsoleConfig::CONFIG,
            $this->sequence('sequences.'.$name, $sequence, $header, $footer, $options)
        );
    }

    private function sequence(
        string $target,
        string|array|callable $sequence,
        string $header,
        string $footer,
        array $options
    ): Append {
        return new Append(
            $target,
            null,
            \is_array($sequence) || \is_callable($sequence)
                ? new CallableSequence($sequence, $header, $footer)
                : new CommandSequence($sequence, $options, $header, $footer)
        );
    }
}
