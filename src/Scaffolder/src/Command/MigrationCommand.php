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

use Spiral\Migrations\Migrator;
use Spiral\Reactor\FileDeclaration;
use Spiral\Scaffolder\Declaration\MigrationDeclaration;
use Spiral\Scaffolder\Exception\ScaffolderException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MigrationCommand extends AbstractCommand
{
    protected const ELEMENT = 'migration';

    protected const NAME        = 'create:migration';
    protected const DESCRIPTION = 'Create migration declaration';
    protected const ARGUMENTS   = [
        ['name', InputArgument::REQUIRED, 'Migration name'],
    ];
    protected const OPTIONS     = [
        [
            'table',
            't',
            InputOption::VALUE_OPTIONAL,
            'Table to be created table',
        ],
        [
            'field',
            'f',
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            'Create field in a format "name:type"',
        ],
        [
            'comment',
            null,
            InputOption::VALUE_OPTIONAL,
            'Optional comment to add as class header',
        ],
    ];

    /**
     * Create migration declaration.
     *
     * @param Migrator $migrator
     * @throws ScaffolderException
     */
    public function perform(Migrator $migrator): void
    {
        /** @var MigrationDeclaration $declaration */
        $declaration = $this->createDeclaration();

        if (!empty($this->option('table'))) {
            $fields = [];
            foreach ($this->option('field') as $field) {
                if (strpos($field, ':') === false) {
                    throw new ScaffolderException("Field definition must in 'name:type' form");
                }

                [$name, $type] = explode(':', $field);
                $fields[$name] = $type;
            }

            $declaration->declareCreation((string)$this->option('table'), $fields);
        }

        $file = new FileDeclaration($this->getNamespace());
        $file->setDirectives('strict_types=1');
        $file->setComment($this->config->headerLines());
        $file->addElement($declaration);

        $filename = $migrator->getRepository()->registerMigration(
            (string)$this->argument('name'),
            $declaration->getName(),
            $file->render()
        );

        $this->writeln(
            "Declaration of '<info>{$declaration->getName()}</info>' "
            . "has been successfully written into '<comment>{$filename}</comment>'."
        );
    }
}
