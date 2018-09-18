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
        // Default commands
        $this->addCommand($modifier, CleanCommand::class);
        $this->addCommand($modifier, ExtensionsCommand::class);

        if ($container->has(TranslatorInterface::class)) {
            $this->addCommand($modifier, IndexCommand::class);
            $this->addCommand($modifier, ExportCommand::class);
            $this->addCommand($modifier, ResetCommand::class);
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
}