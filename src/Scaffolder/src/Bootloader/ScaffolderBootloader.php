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
use Spiral\Scaffolder\Command\BootloaderCommand;
use Spiral\Scaffolder\Command\CommandCommand;
use Spiral\Scaffolder\Command\ConfigCommand;
use Spiral\Scaffolder\Command\ControllerCommand;
use Spiral\Scaffolder\Command\FilterCommand;
use Spiral\Scaffolder\Command\InfoCommand;
use Spiral\Scaffolder\Command\JobHandlerCommand;
use Spiral\Scaffolder\Command\MiddlewareCommand;
use Spiral\Scaffolder\Config\ScaffolderConfig;
use Spiral\Scaffolder\Declaration;
use Spiral\Scaffolder\Declaration\BootloaderDeclaration;
use Spiral\Scaffolder\Declaration\CommandDeclaration;
use Spiral\Scaffolder\Declaration\ConfigDeclaration;
use Spiral\Scaffolder\Declaration\ControllerDeclaration;
use Spiral\Scaffolder\Declaration\FilterDeclaration;
use Spiral\Scaffolder\Declaration\JobHandlerDeclaration;
use Spiral\Scaffolder\Declaration\MiddlewareDeclaration;

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
        $console->addCommand(InfoCommand::class);
        $console->addCommand(BootloaderCommand::class);
        $console->addCommand(CommandCommand::class);
        $console->addCommand(ConfigCommand::class);
        $console->addCommand(ControllerCommand::class);
        $console->addCommand(JobHandlerCommand::class);
        $console->addCommand(MiddlewareCommand::class);
        $console->addCommand(FilterCommand::class);

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
                    BootloaderDeclaration::TYPE => [
                        'namespace' => 'Bootloader',
                        'postfix' => 'Bootloader',
                        'class' => BootloaderDeclaration::class,
                    ],
                    ConfigDeclaration::TYPE => [
                        'namespace' => 'Config',
                        'postfix' => 'Config',
                        'class' => ConfigDeclaration::class,
                        'options' => [
                            'directory' => $dir->get('config'),
                        ],
                    ],
                    ControllerDeclaration::TYPE => [
                        'namespace' => 'Controller',
                        'postfix' => 'Controller',
                        'class' => ControllerDeclaration::class,
                    ],
                    FilterDeclaration::TYPE => [
                        'namespace' => 'Filter',
                        'postfix' => 'Filter',
                        'class' => FilterDeclaration::class,
                    ],
                    MiddlewareDeclaration::TYPE => [
                        'namespace' => 'Middleware',
                        'postfix' => 'Middleware',
                        'class' => MiddlewareDeclaration::class,
                    ],
                    CommandDeclaration::TYPE => [
                        'namespace' => 'Command',
                        'postfix' => 'Command',
                        'class' => CommandDeclaration::class,
                    ],
                    JobHandlerDeclaration::TYPE => [
                        'namespace' => 'Job',
                        'postfix' => 'Job',
                        'class' => JobHandlerDeclaration::class,
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
