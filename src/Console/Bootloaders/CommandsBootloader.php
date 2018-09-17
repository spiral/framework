<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Console\Bootloaders;

use Psr\Container\ContainerInterface;
use Spiral\Commands\CleanCommand;
use Spiral\Commands\ExtensionsCommand;
use Spiral\Commands\Translator\DumpCommand;
use Spiral\Commands\Translator\IndexCommand;
use Spiral\Config\ModifierInterface;
use Spiral\Config\Patches\AppendPatch;
use Spiral\Core\Bootloaders\Bootloader;
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
            $this->addCommand($modifier, DumpCommand::class);
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