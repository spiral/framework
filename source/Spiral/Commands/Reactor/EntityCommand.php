<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Reactor;

use Spiral\Reactor\Generators\DocumentEntityGenerator;
use Symfony\Component\Console\Input\InputArgument;

class EntityCommand extends \Spiral\Commands\Reactor\Prototypes\EntityCommand
{
    /**
     * Success message. To be used by DocumentCommand.
     */
    const SUCCESS_MESSAGE = 'ODM DocumentEntity was successfully created:';

    /**
     * Generator class to be used.
     */
    const GENERATOR = DocumentEntityGenerator::class;

    /**
     * Generation type to be used.
     */
    const TYPE = 'entity';

    /**
     * {@inheritdoc}
     */
    protected $name = 'create:entity';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Generate new ODM document entity.';

    /**
     * {@inheritdoc}
     */
    protected $arguments = [
        ['name', InputArgument::REQUIRED, 'Document name.']
    ];
}