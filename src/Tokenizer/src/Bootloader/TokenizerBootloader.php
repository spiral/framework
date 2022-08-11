<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tokenizer\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Core\Container;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\ClassLocator;
use Spiral\Tokenizer\InvocationLocator;
use Spiral\Tokenizer\InvocationsInterface;
use Spiral\Tokenizer\ScopedClassesInterface;
use Spiral\Tokenizer\ScopedClassLocator;
use Spiral\Tokenizer\Tokenizer;

final class TokenizerBootloader extends Bootloader implements SingletonInterface
{
    protected const BINDINGS = [
        ScopedClassesInterface::class => ScopedClassLocator::class,
        ClassesInterface::class => ClassLocator::class,
        InvocationsInterface::class => InvocationLocator::class,
    ];

    /** @var ConfiguratorInterface */
    private $config;

    public function __construct(ConfiguratorInterface $config)
    {
        $this->config = $config;
    }

    public function boot(Container $container, DirectoriesInterface $dirs): void
    {
        $container->bindInjector(ClassLocator::class, Tokenizer::class);
        $container->bindInjector(InvocationLocator::class, Tokenizer::class);

        $this->config->setDefaults(
            'tokenizer',
            [
                'directories' => [$dirs->get('app')],
                'exclude'     => [
                    $dirs->get('resources'),
                    $dirs->get('config'),
                    'tests',
                    'migrations',
                ],
            ]
        );
    }

    /**
     * Add directory for indexation.
     */
    public function addDirectory(string $directory): void
    {
        $this->config->modify('tokenizer', new Append('directories', null, $directory));
    }
}
