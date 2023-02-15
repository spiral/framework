<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Command;

use Spiral\Console\Attribute\Question;
use Spiral\Scaffolder\Declaration\ConfigDeclaration;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[Question(
    question: 'Please provide the name of the Config class, or the filename of an existing config file',
    argument: 'name'
)]
class ConfigCommand extends AbstractCommand
{
    protected const NAME        = 'create:config';
    protected const DESCRIPTION = 'Create config declaration';
    protected const ARGUMENTS   = [
        [
            'name',
            InputArgument::REQUIRED,
            'config name, or a config filename if "-r" flag is set ({path/to/configs/directory/}{config/filename}.php)',
        ],
    ];

    protected const OPTIONS = [
        [
            'comment',
            'c',
            InputOption::VALUE_OPTIONAL,
            'Optional comment to add as class header',
        ],
        [
            'reverse',
            'r',
            InputOption::VALUE_NONE,
            'Create config class based on a given config filename',
        ],
        [
            'namespace',
            null,
            InputOption::VALUE_OPTIONAL,
            'Optional, specify a custom namespace',
        ],
    ];

    /**
     * Create config declaration.
     */
    public function perform(): int
    {
        $declaration = $this->createDeclaration(ConfigDeclaration::class);

        $declaration->create(
            (bool) $this->option('reverse'),
            (string) $this->argument('name')
        );

        $this->writeDeclaration($declaration);

        return self::SUCCESS;
    }
}
