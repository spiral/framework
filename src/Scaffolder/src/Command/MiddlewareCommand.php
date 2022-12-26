<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Command;

use Spiral\Scaffolder\Declaration\MiddlewareDeclaration;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MiddlewareCommand extends AbstractCommand
{
    protected const NAME        = 'create:middleware';
    protected const DESCRIPTION = 'Create middleware declaration';
    protected const ARGUMENTS   = [
        ['name', InputArgument::REQUIRED, 'Middleware name'],
    ];
    protected const OPTIONS     = [
        [
            'comment',
            'c',
            InputOption::VALUE_OPTIONAL,
            'Optional comment to add as class header',
        ],
        [
            'namespace',
            null,
            InputOption::VALUE_OPTIONAL,
            'Optional, specify a custom namespace',
        ],
    ];

    /**
     * Create middleware declaration.
     */
    public function perform(): int
    {
        $declaration = $this->createDeclaration(MiddlewareDeclaration::class);

        $this->writeDeclaration($declaration);

        return self::SUCCESS;
    }
}
