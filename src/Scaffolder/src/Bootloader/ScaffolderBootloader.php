<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Bootloader;

use Cocur\Slugify\Slugify;
use Cocur\Slugify\SlugifyInterface;
use ReflectionClass;
use ReflectionException;
use Spiral\Boot\Bootloader\Bootloader;
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
        private readonly KernelInterface $kernel
    ) {
    }

    public function init(ConsoleBootloader $console): void
    {
        $console->addCommand(Command\BootloaderCommand::class);
        $console->addCommand(Command\CommandCommand::class);
        $console->addCommand(Command\ConfigCommand::class);
        $console->addCommand(Command\ControllerCommand::class);
        $console->addCommand(Command\FilterCommand::class);
        $console->addCommand(Command\JobHandlerCommand::class);
        $console->addCommand(Command\MiddlewareCommand::class);

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
            'header'       => [
                '{project-name}',
                '',
                '@author {author-name}',
            ],

            /*
             * Base directory for generated classes, class will be automatically localed into sub directory
             * using given namespace.
             */
            'directory'    => directory('app') . 'src/',

            /*
             * Default namespace to be applied for every generated class. By default uses Kernel namespace
             *
             * Example: 'namespace' => 'MyApplication'
             * Controllers: MyApplication\Controllers\SampleController
             */
            'namespace'    => $defaultNamespace,

            /*
             * This is set of default settings to be used for your scaffolding commands.
             */
            'declarations' => [
                Declaration\BootloaderDeclaration::TYPE => [
                    'namespace' => 'Bootloader',
                    'postfix'   => 'Bootloader',
                    'class'     => Declaration\BootloaderDeclaration::class,
                ],
                Declaration\ConfigDeclaration::TYPE => [
                    'namespace' => 'Config',
                    'postfix'   => 'Config',
                    'class'     => Declaration\ConfigDeclaration::class,
                    'options'   => [
                        'directory' => directory('config'),
                    ],
                ],
                Declaration\ControllerDeclaration::TYPE => [
                    'namespace' => 'Controller',
                    'postfix'   => 'Controller',
                    'class'     => Declaration\ControllerDeclaration::class,
                ],
                Declaration\MiddlewareDeclaration::TYPE => [
                    'namespace' => 'Middleware',
                    'postfix'   => '',
                    'class'     => Declaration\MiddlewareDeclaration::class,
                ],
                Declaration\CommandDeclaration::TYPE => [
                    'namespace' => 'Command',
                    'postfix'   => 'Command',
                    'class'     => Declaration\CommandDeclaration::class,
                ],
                Declaration\JobHandlerDeclaration::TYPE => [
                    'namespace' => 'Job',
                    'postfix'   => 'Job',
                    'class'     => Declaration\JobHandlerDeclaration::class,
                ],
                Declaration\FilterDeclaration::TYPE => [
                    'namespace' => 'Request',
                    'postfix'   => 'Request',
                    'class'     => Declaration\FilterDeclaration::class,
                    'options'   => [
                        //Set of default filters and validate rules for various types
                        'mapping' => [
                            'int'     => [
                                'source'    => 'data',
                                'setter'    => 'intval',
                                'validates' => ['notEmpty', 'integer'],
                            ],
                            'integer' => [
                                'source'    => 'data',
                                'setter'    => 'intval',
                                'validates' => ['notEmpty', 'integer'],
                            ],
                            'float'   => [
                                'source'    => 'data',
                                'setter'    => 'floatval',
                                'validates' => ['notEmpty', 'float'],
                            ],
                            'double'  => [
                                'source'    => 'data',
                                'setter'    => 'floatval',
                                'validates' => ['notEmpty', 'float'],
                            ],
                            'string'  => [
                                'source'    => 'data',
                                'setter'    => 'strval',
                                'validates' => ['notEmpty', 'string'],
                            ],
                            'bool'    => [
                                'source'    => 'data',
                                'setter'    => 'boolval',
                                'validates' => ['notEmpty', 'boolean'],
                            ],
                            'boolean' => [
                                'source'    => 'data',
                                'setter'    => 'boolval',
                                'validates' => ['notEmpty', 'boolean'],
                            ],
                            'email'   => [
                                'source'    => 'data',
                                'setter'    => 'strval',
                                'validates' => ['notEmpty', 'string', 'email'],
                            ],
                            'file'    => [
                                'source'    => 'file',
                                'validates' => ['file::uploaded'],
                            ],
                            'image'   => [
                                'source'    => 'file',
                                'validates' => ['image::uploaded', 'image::valid'],
                            ],
                            null      => [
                                'source'    => 'data',
                                'setter'    => 'strval',
                                'validates' => ['notEmpty', 'string'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Register new Scaffolder declaration.
     */
    public function addDeclaration(string $name, array $declaration): void
    {
        $this->config->modify(ScaffolderConfig::CONFIG, new Append('declarations', $name, $declaration));
    }
}
