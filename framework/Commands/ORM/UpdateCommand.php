<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\ORM;

use Spiral\Components\Console\Command;
use Spiral\Components\ORM\SchemaBuilder;

class UpdateCommand extends Command
{
    /**
     * Schema builder instance.
     *
     * @var SchemaBuilder
     */
    public static $schemaBuilder = null;

    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'orm:update';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Update ORM schema and render virtual documentation.';

    /**
     * Update schema and documentation.
     */
    public function perform()
    {
        benchmark('orm:updating');
        self::$schemaBuilder = $this->orm->updateSchema();
        $elapsed = benchmark('orm:updating');

        $this->writeln(
            "<info>ORM Schema and virtual documentation successfully updated " .
            "(<fg=yellow>" . number_format($elapsed, 3) . " s</fg=yellow>).</info>"
        );
    }
}