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
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\InvocationLocator;
use Spiral\Tokenizer\InvocationsInterface;
use Spiral\Tokenizer\Tokenizer;
use Spiral\Tokenizer\TokenizerInterface;

class TokenizerBootloader extends Bootloader
{
    const BOOT = true;

    const BINDINGS = [
        ClassesInterface::class     => ClassLocator::class,
        InvocationsInterface::class => InvocationLocator::class,
        TokenizerInterface::class   => [self::class, 'tokenizer'],
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

    /**
     * @param TokenizerConfig $config
     * @return Tokenizer
     */
    protected function tokenizer(TokenizerConfig $config): Tokenizer
    {
        return new Tokenizer($config);
    }
}