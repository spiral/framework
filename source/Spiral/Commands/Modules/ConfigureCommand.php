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
 * Configure all non-registered modules.
 */
class ConfigureCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'modules:configure';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Configure all non-registered modules';

    public function perform()
    {

    }
}