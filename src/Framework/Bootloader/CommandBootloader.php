<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Bootloader;

use Cycle\Database\DatabaseProviderInterface;
use Cycle\ORM\ORMInterface;
use Psr\Container\ContainerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Command\Cycle;
use Spiral\Command\Database;
use Spiral\Command\Encrypter;
use Spiral\Command\GRPC;
use Spiral\Command\Migrate;
use Spiral\Command\Router;
use Spiral\Command\Translator;
use Spiral\Command\Views;
use Spiral\Console;
use Spiral\Console\Bootloader\ConsoleBootloader;
use Spiral\Console\Sequence\RuntimeDirectory;
use Spiral\Core\Container;
use Spiral\Encrypter\EncryptionInterface;
use Spiral\Files\FilesInterface;
use Spiral\GRPC\InvokerInterface;
use Spiral\Migrations\Migrator;
use Spiral\Router\RouterInterface;
use Spiral\Translator\Config\TranslatorConfig;
use Spiral\Translator\TranslatorInterface;
use Spiral\Views\ViewsInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Register framework directories in tokenizer in order to locate default commands.
 */
final class CommandBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        ConsoleBootloader::class,
    ];

    /**
     * @param ConsoleBootloader $console
     * @param Container         $container
     */
    public function boot(ConsoleBootloader $console, Container $container): void
    {
        $console->addCommand(Console\Command\ConfigureCommand::class);
        $console->addCommand(Console\Command\UpdateCommand::class);

        $console->addConfigureSequence(
            [RuntimeDirectory::class, 'ensure'],
            '<fg=magenta>[runtime]</fg=magenta> <fg=cyan>verify `runtime` directory access</fg=cyan>'
        );

        $this->configureExtensions($console, $container);
    }

    /**
     * @return array
     */
    public function defineDependencies(): array
    {
        return [
            ConsoleBootloader::class,
        ];
    }

    /**
     * @param ConsoleBootloader $console
     * @param Container         $container
     */
    private function configureExtensions(ConsoleBootloader $console, Container $container): void
    {
        if ($container->has(DatabaseProviderInterface::class)) {
            $this->configureDatabase($console);
        }

        if ($container->has(ORMInterface::class)) {
            $this->configureCycle($console, $container);
        }

        if ($container->has(TranslatorInterface::class)) {
            $this->configureTranslator($console);
        }

        if ($container->has(ViewsInterface::class)) {
            $this->configureViews($console);
        }

        if ($container->has(Migrator::class)) {
            $this->configureMigrations($console);
        }

        if ($container->has(InvokerInterface::class)) {
            $this->configureGRPC($console);
        }

        if ($container->has(EncryptionInterface::class)) {
            $this->configureEncrypter($console);
        }

        if ($container->has(RouterInterface::class)) {
            $console->addCommand(Router\ListCommand::class);
        }
    }

    /**
     * @deprecated since v2.12. Will be moved to spiral/cycle-bridge and removed in v3.0
     * @param ConsoleBootloader $console
     */
    private function configureDatabase(ConsoleBootloader $console): void
    {
        $console->addCommand(Database\ListCommand::class, true);
        $console->addCommand(Database\TableCommand::class, true);
    }

    /**
     * @deprecated since v2.9. Will be moved to spiral/cycle-bridge and removed in v3.0
     * @param ConsoleBootloader  $console
     * @param ContainerInterface $container
     */
    private function configureCycle(ConsoleBootloader $console, ContainerInterface $container): void
    {
        $console->addCommand(Cycle\UpdateCommand::class, true);

        $console->addUpdateSequence(
            'cycle',
            '<fg=magenta>[cycle]</fg=magenta> <fg=cyan>update Cycle schema...</fg=cyan>'
        );

        $console->addCommand(Cycle\SyncCommand::class, true);

        if ($container->has(Migrator::class)) {
            $console->addCommand(Cycle\MigrateCommand::class, true);
        }
    }

    /**
     * @param ConsoleBootloader $console
     **/
    private function configureTranslator(ConsoleBootloader $console): void
    {
        $console->addCommand(Translator\IndexCommand::class);
        $console->addCommand(Translator\ExportCommand::class);
        $console->addCommand(Translator\ResetCommand::class);

        $console->addConfigureSequence(
            function (FilesInterface $files, TranslatorConfig $config, OutputInterface $output): void {
                $files->ensureDirectory($config->getLocaleDirectory($config->getDefaultLocale()));
                $output->writeln('<info>The default locale directory has been ensured.</info>');
            },
            '<fg=magenta>[i18n]</fg=magenta> <fg=cyan>ensure default locale directory...</fg=cyan>'
        );

        $console->addConfigureSequence(
            'i18n:index',
            '<fg=magenta>[i18n]</fg=magenta> <fg=cyan>scan translator function and [[values]] usage...</fg=cyan>'
        );
    }

    /**
     * @param ConsoleBootloader $console
     */
    private function configureViews(ConsoleBootloader $console): void
    {
        $console->addCommand(Views\ResetCommand::class);
        $console->addCommand(Views\CompileCommand::class);

        $console->addConfigureSequence(
            'views:compile',
            '<fg=magenta>[views]</fg=magenta> <fg=cyan>warm up view cache...</fg=cyan>'
        );
    }

    /**
     * @deprecated since v2.12. Will be moved to spiral/cycle-bridge and removed in v3.0
     * @param ConsoleBootloader $console
     */
    private function configureMigrations(ConsoleBootloader $console): void
    {
        $console->addCommand(Migrate\InitCommand::class, true);
        $console->addCommand(Migrate\StatusCommand::class, true);
        $console->addCommand(Migrate\MigrateCommand::class, true);
        $console->addCommand(Migrate\RollbackCommand::class, true);
        $console->addCommand(Migrate\ReplayCommand::class, true);
    }

    /**
     * @deprecated since v2.12. Will be moved to spiral/roadrunner-bridge and removed in v3.0
     * @param ConsoleBootloader $console
     */
    private function configureGRPC(ConsoleBootloader $console): void
    {
        $console->addCommand(GRPC\GenerateCommand::class, true);
        $console->addCommand(GRPC\ListCommand::class, true);
    }

    /**
     * @param ConsoleBootloader $console
     */
    private function configureEncrypter(ConsoleBootloader $console): void
    {
        $console->addCommand(Encrypter\KeyCommand::class);
    }
}
