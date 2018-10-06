<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Bootloader\System;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Bootloader\Bootloader;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\ClassLocator;
use Spiral\Tokenizer\InvocationLocator;
use Spiral\Tokenizer\InvocationsInterface;
use Spiral\Tokenizer\Tokenizer;
use Spiral\Tokenizer\TokenizerInterface;

class TokenizerBootloader extends Bootloader
{
    const BOOT = true;

    const BINDINGS = [
        TokenizerInterface::class   => Tokenizer::class,
        ClassesInterface::class     => ClassLocator::class,
        InvocationsInterface::class => InvocationLocator::class
    ];

    /**
     * @param ConfiguratorInterface $configurator
     * @param DirectoriesInterface  $directories
     */
    public function boot(ConfiguratorInterface $configurator, DirectoriesInterface $directories)
    {
        $configurator->setDefaults('tokenizer', [
            'directories' => [$directories->get('app')],
            'exclude'     => [
                $directories->get('resources'),
                $directories->get('config'),
                'tests'
            ]
        ]);
    }
}