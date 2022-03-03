<?php

/**
 * Spiral Framework. Scaffolder
 *
 * @license MIT
 * @author  Valentin V (vvval)
 */

declare(strict_types=1);

namespace Spiral\Scaffolder\Command\Database;

use Spiral\Scaffolder\Command\AbstractCommand;
use Spiral\Scaffolder\Declaration\Database\RepositoryDeclaration;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * @deprecated since v2.10. Will be moved to spiral/cycle-bridge and removed in v3.0
 */
class RepositoryCommand extends AbstractCommand
{
    public const ELEMENT = 'repository';

    protected const NAME        = 'create:repository';
    protected const DESCRIPTION = 'Create repository declaration';
    protected const ARGUMENTS   = [
        ['name', InputArgument::REQUIRED, 'Repository name'],
    ];
    protected const OPTIONS     = [
        [
            'comment',
            'c',
            InputOption::VALUE_OPTIONAL,
            'Optional comment to add as class header',
        ],
    ];

    /**
     * Create repository declaration.
     */
    public function perform(): void
    {
        /** @var RepositoryDeclaration $declaration */
        $declaration = $this->createDeclaration();

        $this->writeDeclaration($declaration);
    }
}
