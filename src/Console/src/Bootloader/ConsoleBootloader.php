<?php

declare(strict_types=1);

namespace Spiral\Console\Bootloader;

use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Config\Patch\Prepend;
use Spiral\Console\CommandCore;
use Spiral\Console\CommandCoreFactory;
use Spiral\Console\CommandLocatorListener;
use Spiral\Console\Config\ConsoleConfig;
use Spiral\Console\Confirmation\ApplicationInProduction;
use Spiral\Console\Console;
use Spiral\Console\ConsoleDispatcher;
use Spiral\Console\Sequence\CallableSequence;
use Spiral\Console\Sequence\CommandSequence;
use Spiral\Core\Attribute\Singleton;
use Spiral\Core\BinderInterface;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Tokenizer\Bootloader\TokenizerListenerBootloader;
use Spiral\Tokenizer\TokenizerListenerRegistryInterface;

/**
 * Bootloads console and provides ability to register custom bootload commands.
 */
#[Singleton]
final class ConsoleBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        TokenizerListenerBootloader::class,
    ];

    protected const SINGLETONS = [
        Console::class => Console::class,
        // LocatorInterface::class => CommandLocator::class,
    ];

    public function __construct(
        private readonly ConfiguratorInterface $config
    ) {
    }

    public function init(AbstractKernel $kernel, BinderInterface $binder): void
    {
        // Lowest priority
        $kernel->bootstrapped(static function (AbstractKernel $kernel): void {
            $kernel->addDispatcher(ConsoleDispatcher::class);
        });

        // Registering necessary scope bindings
        $commandBinder = $binder->getBinder('console.command');
        $commandBinder->bindSingleton(ApplicationInProduction::class, ApplicationInProduction::class);
        $commandBinder->bindSingleton(CommandCoreFactory::class, CommandCoreFactory::class);
        $commandBinder->bindSingleton(CommandCore::class, CommandCore::class);

        $binder->getBinder('console')->bindSingleton(Console::class, Console::class);

        $this->config->setDefaults(
            ConsoleConfig::CONFIG,
            [
                'commands' => [],
                'sequences' => [],
            ]
        );
    }

    public function boot(TokenizerListenerRegistryInterface $listenerRegistry, CommandLocatorListener $listener): void
    {
        $listenerRegistry->addListener($listener);
    }

    /**
     * @param class-string<CoreInterceptorInterface>|string $interceptor
     */
    public function addInterceptor(string $interceptor): void
    {
        $this->config->modify(
            ConsoleConfig::CONFIG,
            new Append('interceptors', null, $interceptor)
        );
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
        if (!isset($this->config->getConfig(ConsoleConfig::CONFIG)['sequences'][$name])) {
            $this->config->modify(
                ConsoleConfig::CONFIG,
                new Append('sequences', $name, [])
            );
        }

        $this->config->modify(
            ConsoleConfig::CONFIG,
            $this->sequence('sequences.' . $name, $sequence, $header, $footer, $options)
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
            \is_string($sequence) ? $sequence : null,
            \is_array($sequence) || \is_callable($sequence)
                ? new CallableSequence($sequence, $header, $footer)
                : new CommandSequence($sequence, $options, $header, $footer)
        );
    }
}
