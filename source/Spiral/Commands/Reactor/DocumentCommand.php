<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Reactor;

use Spiral\Reactor\Generators\DocumentGenerator;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Generate ORM record with pre-defined schema and validation placeholders.
 */
class DocumentCommand extends RecordCommand
{
    /**
     * Generator class to be used.
     */
    const GENERATOR = DocumentGenerator::class;

    /**
     * Generation type to be used.
     */
    const TYPE = 'entity';

    /**
     * {@inheritdoc}
     */
    protected $name = 'create:document';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Generate new ODM document.';

    /**
     * {@inheritdoc}
     */
    protected $arguments = [
        ['name', InputArgument::REQUIRED, 'Document name.']
    ];
}