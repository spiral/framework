<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Console\Bootloader;

use Symfony\Component\Console\Command\Command;
use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Command\CleanCommand;
use Spiral\Command\PublishCommand;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Config\Patch\Prepend;
use Spiral\Console\CommandLocator;
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

    private ConfiguratorInterface $config;

    public function __construct(ConfiguratorInterface $config)
    {
        $this->config = $config;
    }

    public function boot(AbstractKernel $kernel, FactoryInterface $factory): void
    {
        // Lowest priority
        $kernel->started(static function (AbstractKernel $kernel) use ($factory): void {
            $kernel->addDispatcher($factory->make(ConsoleDispatcher::class));
        });

        $this->config->setDefaults(
            'console',
            [
                'commands' => [],
                'configure' => [],
                'update' => [],
            ]
        );

        $this->addCommand(CleanCommand::class);
        $this->addCommand(PublishCommand::class);
    }

    /**
     * @param class-string<Command> $command
     * @param bool $lowPriority A low priority command will be overwritten in a name conflict case.
     *        The parameter might be removed in the next major update.
     */
    public function addCommand(string $command, bool $lowPriority = false): void
    {
        $this->config->modify(
            'console',
            $lowPriority
                ? new Prepend('commands', null, $command)
                : new Append('commands', null, $command)
        );
    }

    /**
     * @param array|string $sequence
     */
    public function addConfigureSequence(
        $sequence,
        string $header,
        string $footer = '',
        array $options = []
    ): void {
        $this->config->modify(
            'console',
            $this->sequence('configure', $sequence, $header, $footer, $options)
        );
    }

    /**
     * @param array|string $sequence
     */
    public function addUpdateSequence(
        $sequence,
        string $header,
        string $footer = '',
        array $options = []
    ): void {
        $this->config->modify(
            'console',
            $this->sequence('update', $sequence, $header, $footer, $options)
        );
    }

    /**
     * @param mixed $sequence
     */
    private function sequence(
        string $target,
        $sequence,
        string $header,
        string $footer,
        array $options
    ): Append {
        if (is_array($sequence) || $sequence instanceof \Closure) {
            return new Append(
                $target,
                null,
                new CallableSequence($sequence, $options, $header, $footer)
            );
        }

        return new Append(
            $target,
            null,
            new CommandSequence($sequence, $options, $header, $footer)
        );
    }
}
