<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */

namespace Spiral\Commands\Reactor;


use Spiral\Commands\Reactor\Prototypes\EntityCommand;
use Spiral\Reactor\Generators\EmbeddableGenerator;
use Symfony\Component\Console\Input\InputArgument;

class EmbeddableCommand extends EntityCommand
{
    /**
     * Success message. To be used by DocumentCommand.
     */
    const SUCCESS_MESSAGE = 'Embeddable ODM Document was successfully created:';

    /**
     * Generator class to be used.
     */
    const GENERATOR = EmbeddableGenerator::class;

    /**
     * Generation type to be used.
     */
    const TYPE = 'entity';

    /**
     * {@inheritdoc}
     */
    protected $name = 'create:embeddable';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Generate new embeddable ODM document.';

    /**
     * {@inheritdoc}
     */
    protected $arguments = [
        ['name', InputArgument::REQUIRED, 'Document name.']
    ];
}