<?php

/**
 * Spiral Framework. Scaffolder
 *
 * @license MIT
 * @author  Valentin V (vvval)
 */

declare(strict_types=1);

namespace Spiral\Scaffolder\Command;

use Spiral\Scaffolder\Declaration\BootloaderDeclaration;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class BootloaderCommand extends AbstractCommand
{
    protected const ELEMENT = 'bootloader';

    protected const NAME        = 'create:bootloader';
    protected const DESCRIPTION = 'Create bootloader declaration';
    protected const ARGUMENTS   = [
        ['name', InputArgument::REQUIRED, 'bootloader name']
    ];
    protected const OPTIONS     = [
        [
            'comment',
            'c',
            InputOption::VALUE_OPTIONAL,
            'Optional comment to add as class header'
        ]
    ];

    /**
     * Create bootloader declaration.
     */
    public function perform(): void
    {
        /** @var BootloaderDeclaration $declaration */
        $declaration = $this->createDeclaration();

        $this->writeDeclaration($declaration);
    }
}
