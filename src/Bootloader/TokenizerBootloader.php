<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Container;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\ClassLocator;
use Spiral\Tokenizer\InvocationLocator;
use Spiral\Tokenizer\InvocationsInterface;
use Spiral\Tokenizer\Tokenizer;

final class TokenizerBootloader extends Bootloader
{
    const BOOT = true;

    const BINDINGS = [
        ClassesInterface::class     => ClassLocator::class,
        InvocationsInterface::class => InvocationLocator::class
    ];

    /**
     * @param Container             $container
     * @param ConfiguratorInterface $cfg
     * @param DirectoriesInterface  $dirs
     */
    public function boot(
        Container $container,
        ConfiguratorInterface $cfg,
        DirectoriesInterface $dirs
    ) {
        $container->bindInjector(ClassLocator::class, Tokenizer::class);
        $container->bindInjector(InvocationLocator::class, Tokenizer::class);

        $cfg->setDefaults('tokenizer', [
            'directories' => [$dirs->get('app')],
            'exclude'     => [
                $dirs->get('resources'),
                $dirs->get('config'),
                'tests'
            ]
        ]);
    }
}