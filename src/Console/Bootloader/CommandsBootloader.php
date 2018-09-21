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
use Spiral\Console\Command\ConfigureCommand;
use Spiral\Console\Command\ReloadCommand;
use Spiral\Console\Command\UpdateCommand;
use Spiral\Console\ConsoleConfigurator;
use Spiral\Console\Sequence\RuntimeDirectory;
use Spiral\Core\Bootloader\Bootloader;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Database\DatabaseInterface;
use Spiral\Filters\MapperInterface;
use Spiral\Translator\TranslatorInterface;

/**
 * Register framework directories in tokenizer in order to locate default commands.
 */
class CommandsBootloader extends Bootloader implements SingletonInterface
{
    const BOOT = true;

    /**
     * @param ModifierInterface  $modifier
     * @param ContainerInterface $container
     */
    public function boot(ModifierInterface $modifier, ContainerInterface $container)
    {
        $console = new ConsoleConfigurator($modifier);

        $console->addCommand(ReloadCommand::class);
        $console->addCommand(ConfigureCommand::class);
        $console->addCommand(UpdateCommand::class);

        $console->addCommand(CleanCommand::class);
        $console->addCommand(ExtensionsCommand::class);

        $console->configureSequence(
            [RuntimeDirectory::class, 'ensure'],
            '<fg=magenta>[runtime]</fg=magenta> <fg=cyan>ensure `runtime` directory access</fg=cyan>'
        );

        $console->configureSequence(
            'console:reload',
            '<fg=magenta>[console]</fg=magenta> <fg=cyan>re-index available console commands...</fg=cyan>'
        );

        if ($container->has(TranslatorInterface::class)) {
            $this->configureTranslator($console);
        }

        if ($container->has(MapperInterface::class)) {
            $this->configureFilters($console);
        }

        if ($container->has(DatabaseInterface::class)) {
            $this->configureDatabase($console);
        }
    }

    /**
     * @param ConsoleConfigurator $console
     */
    private function configureTranslator(ConsoleConfigurator $console)
    {
        $console->addCommand(IndexCommand::class);
        $console->addCommand(ExportCommand::class);
        $console->addCommand(ResetCommand::class);

        $console->configureSequence(
            'i18n:reset',
            '<fg=magenta>[i18n]</fg=magenta> <fg=cyan>reset translator locales cache...</fg=cyan>'
        );

        $console->configureSequence(
            'i18n:index',
            '<fg=magenta>[i18n]</fg=magenta> <fg=cyan>scan translator function and [[values]] usage...</fg=cyan>'
        );
    }

    /**
     * @param ConsoleConfigurator $console
     */
    private function configureFilters(ConsoleConfigurator $console)
    {
        $console->addCommand(\Spiral\Command\Filters\UpdateCommand::class);

        $console->updateSequence(
            'filter:update',
            '<fg=magenta>[filters]</fg=magenta> <fg=cyan>update filters mapping schema</fg=cyan>'
        );
    }

    /**
     * @param ConsoleConfigurator $console
     */
    private function configureDatabase(ConsoleConfigurator $console)
    {
        $console->addCommand(\Spiral\Command\Database\ListCommand::class);
    }
}