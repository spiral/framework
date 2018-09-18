<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Console\Bootloader;

use Psr\Container\ContainerInterface;
use Spiral\Command\CleanCommand;
use Spiral\Command\ExtensionsCommand;
use Spiral\Command\Translator\ExportCommand;
use Spiral\Command\Translator\IndexCommand;
use Spiral\Command\Translator\ResetCommand;
use Spiral\Config\ModifierInterface;
use Spiral\Config\Patch\AppendPatch;
use Spiral\Console\Command\ConfigureCommand;
use Spiral\Console\Command\UpdateCommand;
use Spiral\Console\Sequence\CallableSequence;
use Spiral\Console\Sequence\CommandSequence;
use Spiral\Core\Bootloader\Bootloader;
use Spiral\Translator\TranslatorInterface;

/**
 * Register framework directories in tokenizer in order to locate default commands.
 */
class CommandsBootloader extends Bootloader
{
    const BOOT = true;

    /**
     * @param ModifierInterface  $modifier
     * @param ContainerInterface $container
     */
    public function boot(ModifierInterface $modifier, ContainerInterface $container)
    {
        // Console commands
        $this->addCommand($modifier, ConfigureCommand::class);
        $this->addCommand($modifier, UpdateCommand::class);

        // Default commands
        $this->addCommand($modifier, CleanCommand::class);
        $this->addCommand($modifier, ExtensionsCommand::class);

        // Registering configure sequences
        $this->addCommandSequence(
            $modifier,
            'configure',
            'console:reload',
            [],
            '<fg=magenta>[console]</fg=magenta> <fg=cyan>re-index available console commands...</fg=cyan>'
        );

        if ($container->has(TranslatorInterface::class)) {
            $this->addCommand($modifier, IndexCommand::class);
            $this->addCommand($modifier, ExportCommand::class);
            $this->addCommand($modifier, ResetCommand::class);

            $this->addCommandSequence(
                $modifier,
                'configure',
                'i18n:reset',
                [],
                '<fg=magenta>[i18n]</fg=magenta> <fg=cyan>reset translator locales cache...</fg=cyan>');

            $this->addCommandSequence(
                $modifier,
                'configure',
                'i18n:index',
                [],
                '<fg=magenta>[i18n]</fg=magenta> <fg=cyan>scan translator function and [[values]] usage...</fg=cyan>'
            );
        }
    }

    /**
     * @param ModifierInterface $modifier
     * @param string            $command
     */
    private function addCommand(ModifierInterface $modifier, string $command)
    {
        $modifier->modify('console', new AppendPatch('commands', null, $command));
    }

    /**
     * @param ModifierInterface $modifier
     * @param string            $target
     * @param string            $command
     * @param array             $options
     * @param string            $header
     * @param string            $footer
     */
    private function addCommandSequence(
        ModifierInterface $modifier,
        string $target,
        string $command,
        array $options = [],
        string $header = '',
        string $footer = ''
    ) {
        $modifier->modify(
            "console",
            new AppendPatch($target, null, new CommandSequence($command, $options, $header, $footer))
        );
    }

    /**
     * @param ModifierInterface $modifier
     * @param string            $target
     * @param                   $function
     * @param array             $parameters
     * @param string            $header
     * @param string            $footer
     */
    private function addCallableSequence(
        ModifierInterface $modifier,
        string $target,
        $function,
        array $parameters = [],
        string $header = '',
        string $footer = ''
    ) {
        $modifier->modify(
            "console",
            new AppendPatch($target, null, new CallableSequence($function, $parameters, $header, $footer))
        );
    }
}