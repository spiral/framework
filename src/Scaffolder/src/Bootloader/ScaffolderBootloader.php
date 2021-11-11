<?php

/**
 * Spiral Framework. Scaffolder
 *
 * @license MIT
 * @author  Valentin V (vvval)
 */

declare(strict_types=1);

namespace Spiral\Scaffolder\Bootloader;

use Cocur\Slugify\Slugify;
use Cocur\Slugify\SlugifyInterface;
use ReflectionClass;
use ReflectionException;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\KernelInterface;
use Spiral\Bootloader\ConsoleBootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Scaffolder\Command;
use Spiral\Scaffolder\Declaration;

class ScaffolderBootloader extends Bootloader
{
    protected const BINDINGS = [
        SlugifyInterface::class => Slugify::class,
    ];

    /** @var ConfiguratorInterface */
    private $config;

    /** @var KernelInterface */
    private $kernel;

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
        $console->addCommand(Command\Database\EntityCommand::class);
        $console->addCommand(Command\Database\RepositoryCommand::class);
        $console->addCommand(Command\BootloaderCommand::class);
        $console->addCommand(Command\CommandCommand::class);
        $console->addCommand(Command\ConfigCommand::class);
        $console->addCommand(Command\ControllerCommand::class);
        $console->addCommand(Command\FilterCommand::class);
        $console->addCommand(Command\JobHandlerCommand::class);
        $console->addCommand(Command\MiddlewareCommand::class);
        $console->addCommand(Command\MigrationCommand::class);

        try {
            $defaultNamespace = (new ReflectionClass($this->kernel))->getNamespaceName();
        } catch (ReflectionException $e) {
            $defaultNamespace = '';
        }

        $this->config->setDefaults('scaffolder', [
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
                    'class'     => Declaration\BootloaderDeclaration::class,
                ],
                'config'     => [
                    'namespace' => 'Config',
                    'postfix'   => 'Config',
                    'class'     => Declaration\ConfigDeclaration::class,
                    'options'   => [
                        'directory' => directory('config'),
                    ],
                ],
                'controller' => [
                    'namespace' => 'Controller',
                    'postfix'   => 'Controller',
                    'class'     => Declaration\ControllerDeclaration::class,
                ],
                'middleware' => [
                    'namespace' => 'Middleware',
                    'postfix'   => '',
                    'class'     => Declaration\MiddlewareDeclaration::class,
                ],
                'command'    => [
                    'namespace' => 'Command',
                    'postfix'   => 'Command',
                    'class'     => Declaration\CommandDeclaration::class,
                ],
                'jobHandler' => [
                    'namespace' => 'Job',
                    'postfix'   => 'Job',
                    'class'     => Declaration\JobHandlerDeclaration::class,
                ],
                'migration'  => [
                    'namespace' => '',
                    'postfix'   => 'Migration',
                    'class'     => Declaration\MigrationDeclaration::class,
                ],
                'filter'     => [
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
                'entity'     => [
                    'namespace' => 'Database',
                    'postfix'   => '',
                    'options'   => [
                        'annotated' => Declaration\Database\Entity\AnnotatedDeclaration::class,
                    ],
                ],
                'repository' => [
                    'namespace' => 'Repository',
                    'postfix'   => 'Repository',
                    'class'     => Declaration\Database\RepositoryDeclaration::class,
                ],
            ],
        ]);
    }
}
