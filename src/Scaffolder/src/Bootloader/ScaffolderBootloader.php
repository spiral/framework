<?php

/**
 * Spiral Framework. Scaffolder
 *
 * @license MIT
 * @author  Valentin V (vvval)
 */

declare(strict_types=1);

namespace Spiral\Scaffolder\Bootloader;

use Spiral\Scaffolder\Command\Database\EntityCommand;
use Spiral\Scaffolder\Command\Database\RepositoryCommand;
use Spiral\Scaffolder\Command\BootloaderCommand;
use Spiral\Scaffolder\Command\CommandCommand;
use Spiral\Scaffolder\Command\ConfigCommand;
use Spiral\Scaffolder\Command\ControllerCommand;
use Spiral\Scaffolder\Command\FilterCommand;
use Spiral\Scaffolder\Command\JobHandlerCommand;
use Spiral\Scaffolder\Command\MiddlewareCommand;
use Spiral\Scaffolder\Command\MigrationCommand;
use Spiral\Scaffolder\Declaration\BootloaderDeclaration;
use Spiral\Scaffolder\Declaration\ConfigDeclaration;
use Spiral\Scaffolder\Declaration\ControllerDeclaration;
use Spiral\Scaffolder\Declaration\MiddlewareDeclaration;
use Spiral\Scaffolder\Declaration\CommandDeclaration;
use Spiral\Scaffolder\Declaration\JobHandlerDeclaration;
use Spiral\Scaffolder\Declaration\MigrationDeclaration;
use Spiral\Scaffolder\Declaration\FilterDeclaration;
use Spiral\Scaffolder\Declaration\Database\Entity\AnnotatedDeclaration;
use Spiral\Scaffolder\Declaration\Database\RepositoryDeclaration;
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

    private ConfiguratorInterface $config;

    private KernelInterface $kernel;

    /**
     * ScaffolderBootloader constructor.
     */
    public function __construct(ConfiguratorInterface $config, KernelInterface $kernel)
    {
        $this->config = $config;
        $this->kernel = $kernel;
    }

    public function boot(ConsoleBootloader $console): void
    {
        $console->addCommand(EntityCommand::class, true);
        $console->addCommand(RepositoryCommand::class, true);
        $console->addCommand(BootloaderCommand::class);
        $console->addCommand(CommandCommand::class);
        $console->addCommand(ConfigCommand::class);
        $console->addCommand(ControllerCommand::class);
        $console->addCommand(FilterCommand::class);
        $console->addCommand(JobHandlerCommand::class);
        $console->addCommand(MiddlewareCommand::class);
        $console->addCommand(MigrationCommand::class, true);

        try {
            $defaultNamespace = (new ReflectionClass($this->kernel))->getNamespaceName();
        } catch (ReflectionException $e) {
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
                'bootloader' => [
                    'namespace' => 'Bootloader',
                    'postfix'   => 'Bootloader',
                    'class'     => BootloaderDeclaration::class,
                ],
                'config'     => [
                    'namespace' => 'Config',
                    'postfix'   => 'Config',
                    'class'     => ConfigDeclaration::class,
                    'options'   => [
                        'directory' => directory('config'),
                    ],
                ],
                'controller' => [
                    'namespace' => 'Controller',
                    'postfix'   => 'Controller',
                    'class'     => ControllerDeclaration::class,
                ],
                'middleware' => [
                    'namespace' => 'Middleware',
                    'postfix'   => '',
                    'class'     => MiddlewareDeclaration::class,
                ],
                'command'    => [
                    'namespace' => 'Command',
                    'postfix'   => 'Command',
                    'class'     => CommandDeclaration::class,
                ],
                'jobHandler' => [
                    'namespace' => 'Job',
                    'postfix'   => 'Job',
                    'class'     => JobHandlerDeclaration::class,
                ],
                'migration'  => [
                    'namespace' => '',
                    'postfix'   => 'Migration',
                    'class'     => MigrationDeclaration::class,
                ],
                'filter'     => [
                    'namespace' => 'Request',
                    'postfix'   => 'Request',
                    'class'     => FilterDeclaration::class,
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
                'entity'     => [
                    'namespace' => 'Database',
                    'postfix'   => '',
                    'options'   => [
                        'annotated' => AnnotatedDeclaration::class,
                    ],
                ],
                'repository' => [
                    'namespace' => 'Repository',
                    'postfix'   => 'Repository',
                    'class'     => RepositoryDeclaration::class,
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
