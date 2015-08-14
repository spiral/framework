<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Migrations\Prototypes;

use Spiral\Console\Command;
use Spiral\Console\Helpers\ConsoleFormatter;
use Spiral\Database\Entities\Database;

/**
 * Provides ability to display database and schemas operations
 */
class AbstractCommand extends Command
{
    protected function configureLogging(Database $database)
    {
        $database->driver()->setLogger(new ConsoleFormatter(
            $this->output,
            [],
            $database->getName()
        ));
    }
}