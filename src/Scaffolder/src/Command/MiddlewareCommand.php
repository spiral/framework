<?php

/**
 * Spiral Framework. Scaffolder
 *
 * @license MIT
 * @author  Anton Titov (Wolfy-J)
 * @author  Valentin V (vvval)
 */

declare(strict_types=1);

namespace Spiral\Scaffolder\Command;

use Spiral\Scaffolder\Declaration\MiddlewareDeclaration;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MiddlewareCommand extends AbstractCommand
{
    protected const ELEMENT = 'middleware';

    protected const NAME        = 'create:middleware';
    protected const DESCRIPTION = 'Create middleware declaration';
    protected const ARGUMENTS   = [
        ['name', InputArgument::REQUIRED, 'Middleware name']
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
     * Create middleware declaration.
     */
    public function perform(): void
    {
        /** @var MiddlewareDeclaration $declaration */
        $declaration = $this->createDeclaration();

        $this->writeDeclaration($declaration);
    }
}
