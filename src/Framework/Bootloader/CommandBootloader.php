<?php

declare(strict_types=1);

namespace Spiral\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Command\Encrypter;
use Spiral\Command\Router;
use Spiral\Command\Translator;
use Spiral\Command\Views;
use Spiral\Console;
use Spiral\Console\Bootloader\ConsoleBootloader;
use Spiral\Console\Sequence\RuntimeDirectory;
use Spiral\Core\Container;
use Spiral\Encrypter\EncryptionInterface;
use Spiral\Files\FilesInterface;
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
    public function init(ConsoleBootloader $console, Container $container): void
    {
        $console->addCommand(Console\Command\ConfigureCommand::class);
        $console->addCommand(Console\Command\UpdateCommand::class);

        $console->addConfigureSequence(
            [RuntimeDirectory::class, 'ensure'],
            '<fg=magenta>[runtime]</fg=magenta> <fg=cyan>verify `runtime` directory access</fg=cyan>'
        );

        $this->configureExtensions($console, $container);
    }

    private function configureExtensions(ConsoleBootloader $console, Container $container): void
    {
        if ($container->has(TranslatorInterface::class)) {
            $this->configureTranslator($console);
        }

        if ($container->has(ViewsInterface::class)) {
            $this->configureViews($console);
        }

        if ($container->has(EncryptionInterface::class)) {
            $this->configureEncrypter($console);
        }

        if ($container->has(RouterInterface::class)) {
            $console->addCommand(Router\ListCommand::class);
        }
    }

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

    private function configureViews(ConsoleBootloader $console): void
    {
        $console->addCommand(Views\ResetCommand::class);
        $console->addCommand(Views\CompileCommand::class);

        $console->addConfigureSequence(
            'views:compile',
            '<fg=magenta>[views]</fg=magenta> <fg=cyan>warm up view cache...</fg=cyan>'
        );
    }

    private function configureEncrypter(ConsoleBootloader $console): void
    {
        $console->addCommand(Encrypter\KeyCommand::class);
    }
}
