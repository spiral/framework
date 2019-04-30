<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Bootloader;

use Psr\Container\ContainerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\Bootloader\DependedInterface;
use Spiral\Command\Database;
use Spiral\Console;
use Spiral\Console\Sequence\RuntimeDirectory;
use Spiral\Database\DatabaseProviderInterface;

/**
 * Register framework directories in tokenizer in order to locate default commands.
 */
final class CommandsBootloader extends Bootloader implements DependedInterface
{
    /**
     * @param ConsoleBootloader  $console
     * @param ContainerInterface $container
     *
     * @throws \Spiral\Core\Exception\ConfiguratorException
     */
    public function boot(ConsoleBootloader $console, ContainerInterface $container)
    {
        $console->addCommand(Console\Command\ConfigureCommand::class);
        $console->addCommand(Console\Command\UpdateCommand::class);

        $console->registerConfigure(
            [RuntimeDirectory::class, 'ensure'],
            '<fg=magenta>[runtime]</fg=magenta> <fg=cyan>ensure `runtime` directory access</fg=cyan>'
        );

        if ($container->has(DatabaseProviderInterface::class)) {
            $this->configureDatabase($console);
        }

        //        if ($container->has(TranslatorInterface::class)) {
        //            $this->configureTranslator($console);
        //        }
        //
        //        if ($container->has(MapperInterface::class)) {
        //            $this->configureFilters($console);
        //        }
        //

        //
        //        if ($container->has(ViewsInterface::class)) {
        //            $this->configureViews($console);
        //        }
        //
        //        if ($container->has(Migrator::class)) {
        //            $this->configureMigrations($console);
        //        }
    }

    /**
     * @return array
     */
    public function defineDependencies(): array
    {
        return [ConsoleBootloader::class];
    }

    /**
     * @param ConsoleBootloader $console
     *
     * @throws \Spiral\Core\Exception\ConfiguratorException
     */
    private function configureDatabase(ConsoleBootloader $console)
    {
        $console->addCommand(Database\ListCommand::class);
        $console->addCommand(Database\TableCommand::class);
    }

    //    /**
    //     * @param ConsoleConfigurator $console
    //     *
    //     * @throws \Spiral\Core\Exception\ConfiguratorException
    //     */
    //    private function configureTranslator(ConsoleConfigurator $console)
    //    {
    //        $console->addCommand(Translator\IndexCommand::class);
    //        $console->addCommand(Translator\ExportCommand::class);
    //        $console->addCommand(Translator\ResetCommand::class);
    //
    //        $console->configureSequence(
    //            'i18n:index',
    //            '<fg=magenta>[i18n]</fg=magenta> <fg=cyan>scan translator function and [[values]] usage...</fg=cyan>'
    //        );
    //    }
    //
    //    /**
    //     * @param ConsoleConfigurator $console
    //     *
    //     * @throws \Spiral\Core\Exception\ConfiguratorException
    //     */
    //    private function configureFilters(ConsoleConfigurator $console)
    //    {
    //        $console->addCommand(Filters\UpdateCommand::class);
    //
    //        $console->updateSequence(
    //            'filter:update',
    //            '<fg=magenta>[filters]</fg=magenta> <fg=cyan>update filters mapping schema</fg=cyan>'
    //        );
    //    }
    //

    //    /**
    //     * @param ConsoleConfigurator $console
    //     *
    //     * @throws \Spiral\Core\Exception\ConfiguratorException
    //     */
    //    private function configureViews(ConsoleConfigurator $console)
    //    {
    //        $console->addCommand(Views\ResetCommand::class);
    //        $console->addCommand(Views\CompileCommand::class);
    //
    //        $console->configureSequence(
    //            'views:compile',
    //            '<fg=magenta>[views]</fg=magenta> <fg=cyan>warm up view cache...</fg=cyan>'
    //        );
    //    }
    //
    //    /**
    //     * @param ConsoleConfigurator $console
    //     *
    //     * @throws \Spiral\Core\Exception\ConfiguratorException
    //     */
    //    private function configureMigrations(ConsoleConfigurator $console)
    //    {
    //        $console->addCommand(Migrate\InitCommand::class);
    //        $console->addCommand(Migrate\StatusCommand::class);
    //        $console->addCommand(Migrate\MigrateCommand::class);
    //        $console->addCommand(Migrate\RollbackCommand::class);
    //        $console->addCommand(Migrate\ReplayCommand::class);
    //    }
}