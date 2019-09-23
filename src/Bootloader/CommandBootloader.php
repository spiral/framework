<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Bootloader;

use Cycle\ORM\ORMInterface;
use Psr\Container\ContainerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Command\Cycle;
use Spiral\Command\Database;
use Spiral\Command\Encrypter;
use Spiral\Command\GRPC;
use Spiral\Command\Migrate;
use Spiral\Command\Translator;
use Spiral\Command\Views;
use Spiral\Console;
use Spiral\Console\Sequence\RuntimeDirectory;
use Spiral\Core\Container;
use Spiral\Database\DatabaseProviderInterface;
use Spiral\Encrypter\EncryptionInterface;
use Spiral\Files\FilesInterface;
use Spiral\GRPC\InvokerInterface;
use Spiral\Migrations\Migrator;
use Spiral\Translator\Config\TranslatorConfig;
use Spiral\Translator\TranslatorInterface;
use Spiral\Views\ViewsInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Register framework directories in tokenizer in order to locate default commands.
 */
final class CommandBootloader extends Bootloader
{
    const DEPENDENCIES = [
        ConsoleBootloader::class,
    ];

    /**
     * @param ConsoleBootloader $console
     * @param Container         $container
     */
    public function boot(ConsoleBootloader $console, Container $container)
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
            ConsoleBootloader::class
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
    }

    /**
     * @param ConsoleBootloader $console
     */
    private function configureDatabase(ConsoleBootloader $console)
    {
        $console->addCommand(Database\ListCommand::class);
        $console->addCommand(Database\TableCommand::class);
    }

    /**
     * @param ConsoleBootloader  $console
     * @param ContainerInterface $container
     */
    private function configureCycle(ConsoleBootloader $console, ContainerInterface $container)
    {
        $console->addCommand(Cycle\UpdateCommand::class);

        $console->addUpdateSequence(
            'cycle',
            '<fg=magenta>[cycle]</fg=magenta> <fg=cyan>update Cycle schema...</fg=cyan>'
        );

        $console->addCommand(Cycle\SyncCommand::class);

        if ($container->has(Migrator::class)) {
            $console->addCommand(Cycle\MigrateCommand::class);
        }
    }

    /**
     * @param ConsoleBootloader $console
     **/
    private function configureTranslator(ConsoleBootloader $console)
    {
        $console->addCommand(Translator\IndexCommand::class);
        $console->addCommand(Translator\ExportCommand::class);
        $console->addCommand(Translator\ResetCommand::class);

        $console->addConfigureSequence(
            function (FilesInterface $files, TranslatorConfig $config, OutputInterface $output) {
                $files->ensureDirectory($config->getLocaleDirectory($config->getDefaultLocale()));
                $output->writeln("<info>The default locale directory has been ensured.</info>");
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
    private function configureViews(ConsoleBootloader $console)
    {
        $console->addCommand(Views\ResetCommand::class);
        $console->addCommand(Views\CompileCommand::class);

        $console->addConfigureSequence(
            'views:compile',
            '<fg=magenta>[views]</fg=magenta> <fg=cyan>warm up view cache...</fg=cyan>'
        );
    }

    /**
     * @param ConsoleBootloader $console
     */
    private function configureMigrations(ConsoleBootloader $console)
    {
        $console->addCommand(Migrate\InitCommand::class);
        $console->addCommand(Migrate\StatusCommand::class);
        $console->addCommand(Migrate\MigrateCommand::class);
        $console->addCommand(Migrate\RollbackCommand::class);
        $console->addCommand(Migrate\ReplayCommand::class);
    }

    /**
     * @param ConsoleBootloader $console
     */
    private function configureGRPC(ConsoleBootloader $console)
    {
        $console->addCommand(GRPC\GenerateCommand::class);
        $console->addCommand(GRPC\ListCommand::class);
    }

    /**
     * @param ConsoleBootloader $console
     */
    private function configureEncrypter(ConsoleBootloader $console)
    {
        $console->addCommand(Encrypter\KeyCommand::class);
    }
}
