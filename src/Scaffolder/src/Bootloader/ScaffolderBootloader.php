<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Bootloader;

use Cocur\Slugify\Slugify;
use Cocur\Slugify\SlugifyInterface;
use ReflectionClass;
use ReflectionException;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\KernelInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Console\Bootloader\ConsoleBootloader;
use Spiral\Scaffolder\Command;
use Spiral\Scaffolder\Config\ScaffolderConfig;
use Spiral\Scaffolder\Declaration;

class ScaffolderBootloader extends Bootloader
{
    protected const BINDINGS = [
        SlugifyInterface::class => Slugify::class,
    ];

    public function __construct(
        private readonly ConfiguratorInterface $config,
        private readonly KernelInterface $kernel,
    ) {
    }

    public function init(ConsoleBootloader $console, DirectoriesInterface $dir): void
    {
        $console->addCommand(Command\InfoCommand::class);
        $console->addCommand(Command\BootloaderCommand::class);
        $console->addCommand(Command\CommandCommand::class);
        $console->addCommand(Command\ConfigCommand::class);
        $console->addCommand(Command\ControllerCommand::class);
        $console->addCommand(Command\JobHandlerCommand::class);
        $console->addCommand(Command\MiddlewareCommand::class);
        $console->addCommand(Command\FilterCommand::class);

        try {
            $defaultNamespace = (new ReflectionClass($this->kernel))->getNamespaceName();
        } catch (ReflectionException) {
            $defaultNamespace = '';
        }

        $this->config->setDefaults(ScaffolderConfig::CONFIG, [
            /*
             * This is set of comment lines to be applied to every scaffolded file, you can use env() function
             * to make it developer specific or set one universal pattern per project.
             */
            'header' => [],

            /*
             * Base directory for generated classes, class will be automatically localed into sub directory
             * using given namespace.
             */
            'directory' => $dir->get('app') . 'src/',

            /*
             * Default namespace to be applied for every generated class. By default uses Kernel namespace
             *
             * Example: 'namespace' => 'MyApplication'
             * Controllers: MyApplication\Controllers\SampleController
             */
            'namespace' => $defaultNamespace,

            'declarations' => [],

            /*
             * This is set of default settings to be used for your scaffolding commands.
             */
            'defaults' => [
                'declarations' => [
                    Declaration\BootloaderDeclaration::TYPE => [
                        'namespace' => 'Bootloader',
                        'postfix' => 'Bootloader',
                        'class' => Declaration\BootloaderDeclaration::class,
                    ],
                    Declaration\ConfigDeclaration::TYPE => [
                        'namespace' => 'Config',
                        'postfix' => 'Config',
                        'class' => Declaration\ConfigDeclaration::class,
                        'options' => [
                            'directory' => $dir->get('config'),
                        ],
                    ],
                    Declaration\ControllerDeclaration::TYPE => [
                        'namespace' => 'Controller',
                        'postfix' => 'Controller',
                        'class' => Declaration\ControllerDeclaration::class,
                    ],
                    Declaration\FilterDeclaration::TYPE => [
                        'namespace' => 'Filter',
                        'postfix' => 'Filter',
                        'class' => Declaration\FilterDeclaration::class,
                    ],
                    Declaration\MiddlewareDeclaration::TYPE => [
                        'namespace' => 'Middleware',
                        'postfix' => 'Middleware',
                        'class' => Declaration\MiddlewareDeclaration::class,
                    ],
                    Declaration\CommandDeclaration::TYPE => [
                        'namespace' => 'Command',
                        'postfix' => 'Command',
                        'class' => Declaration\CommandDeclaration::class,
                    ],
                    Declaration\JobHandlerDeclaration::TYPE => [
                        'namespace' => 'Job',
                        'postfix' => 'Job',
                        'class' => Declaration\JobHandlerDeclaration::class,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Register a new Scaffolder declaration.
     *
     * @param non-empty-string $name
     */
    public function addDeclaration(string $name, array $declaration): void
    {
        $this->config->modify(
            ScaffolderConfig::CONFIG,
            new Append('defaults.declarations', $name, $declaration),
        );
    }
}
