<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Migrations\Prototypes;

use Psr\Log\LogLevel;
use Spiral\Console\Command;
use Spiral\Console\Helpers\ConsoleFormatter;
use Spiral\Database\Entities\Database;

/**
 * Provides ability to display database and schemas operations
 */
class AbstractCommand extends Command
{
    /**
     * Driver log formats for verbosity.
     *
     * @var array
     */
    protected $formats = [
        LogLevel::INFO    => 'fg=cyan',
        LogLevel::DEBUG   => '',
        LogLevel::WARNING => 'fg=yellow'
    ];

    /**
     * Enable visual logging for database driver.
     *
     * @param Database $database
     */
    protected function configureLogging(Database $database)
    {
        $database->driver()->setLogger(new ConsoleFormatter(
            $this->output,
            $this->formats,
            $database->getName()
        ));
    }
}