<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Modules;

use Spiral\Console\Command;

/**
 * Publish all registered modules resources.
 */
class PublishCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'modules:publish';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Publish all registered modules resources';

    public function perform()
    {

    }
}